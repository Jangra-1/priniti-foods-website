<?php
/**
 * Shipment lifecycle for one order: create (once), label, cancel, refresh tracking.
 *
 * Duplicate protection when creating:
 *   1. an order with an AWB, or a create in progress, is refused;
 *   2. a lock (an atomic option row on the site) stops two simultaneous clicks;
 *   3. the state is saved as "pending" BEFORE the request is sent;
 *   4. if iThink gives no usable answer (timeout, 5xx, bad JSON) the state becomes "uncertain": iThink may have
 *      booked it, so a new attempt is refused until someone confirms in the iThink panel that it did not.
 * The order number is sent as iThink's "order" reference, so the panel can be searched for it.
 *
 * Storage goes through Priniti_IThink_Order_Store, so tests can run without WooCommerce.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

interface Priniti_IThink_Order_Store {
	public function get( string $key ): string;

	public function set( string $key, string $value ): void;

	public function note( string $text ): void;

	/** Current WooCommerce status without "wc-". */
	public function status(): string;

	public function set_status( string $status, string $note ): void;

	public function save(): void;

	/** Atomic lock for this order; false when already held. */
	public function lock(): bool;

	public function unlock(): void;
}

final class Priniti_IThink_Shipments {
	public const STATE_PENDING   = 'pending';
	public const STATE_CREATED   = 'created';
	public const STATE_UNCERTAIN = 'uncertain';
	public const STATE_FAILED    = 'failed';
	public const STATE_CANCELLED = 'cancelled';

	public function __construct( private Priniti_IThink_Config $config, private Priniti_IThink_Client $client ) {}

	/** True when a shipment exists (or may exist) for the order, so a new one must not be created. */
	public static function blocks_new( Priniti_IThink_Order_Store $store ): bool {
		return '' !== $store->get( Priniti_Meta::ITHINK_AWB )
			|| in_array( $store->get( Priniti_Meta::ITHINK_STATE ), array( self::STATE_PENDING, self::STATE_CREATED, self::STATE_UNCERTAIN ), true );
	}

	/**
	 * Creates the shipment. $order and $package as for Priniti_IThink_Payload::shipment().
	 *
	 * @param bool $checked_panel The manager confirmed in the iThink panel that an "uncertain" attempt created nothing.
	 * @return array{awb: string, courier: string, refnum: string, tracking_url: string}
	 */
	public function create( Priniti_IThink_Order_Store $store, array $order, array $package, bool $checked_panel = false ): array {
		$state = $store->get( Priniti_Meta::ITHINK_STATE );
		if ( '' !== $store->get( Priniti_Meta::ITHINK_AWB ) || self::STATE_CREATED === $state ) {
			throw new Priniti_IThink_Error( 'duplicate', 'This order already has an iThink shipment.' );
		}
		if ( self::STATE_PENDING === $state ) {
			throw new Priniti_IThink_Error( 'duplicate', 'A shipment request for this order is already in progress. Check the iThink panel before trying again.' );
		}
		if ( self::STATE_UNCERTAIN === $state && ! $checked_panel ) {
			throw new Priniti_IThink_Error( 'uncertain', 'The last shipment request got no answer from iThink. Search the iThink panel for this order number; only retry after confirming no shipment was created.' );
		}

		// Validate before locking: a missing weight or address never reaches iThink.
		$shipment = Priniti_IThink_Payload::shipment( $order, $package, $this->config );

		if ( ! $store->lock() ) {
			throw new Priniti_IThink_Error( 'duplicate', 'A shipment request for this order is already in progress.' );
		}
		try {
			$store->set( Priniti_Meta::ITHINK_STATE, self::STATE_PENDING );
			$store->set( Priniti_Meta::ITHINK_ENV, $this->config->env() );
			$store->set( Priniti_Meta::ITHINK_PACKAGE, (string) json_encode( array_intersect_key( $package, array_flip( array( 'weight_g', 'length_cm', 'width_cm', 'height_cm' ) ) ) ) );
			$store->set( Priniti_Meta::ITHINK_ERROR, '' );
			$store->save();

			try {
				$result = $this->client->create_shipment( $shipment );
			} catch ( Priniti_IThink_Error $e ) {
				$uncertain = $e->unanswered();
				$store->set( Priniti_Meta::ITHINK_STATE, $uncertain ? self::STATE_UNCERTAIN : self::STATE_FAILED );
				$store->set( Priniti_Meta::ITHINK_ERROR, $e->getMessage() );
				$store->note(
					$uncertain
						? sprintf( 'iThink (%s) did not confirm the shipment request: %s Check the iThink panel for this order before retrying.', $this->config->env(), $e->getMessage() )
						: sprintf( 'iThink (%s) shipment not created: %s', $this->config->env(), $e->getMessage() )
				);
				$store->save();
				throw $e;
			}

			$store->set( Priniti_Meta::ITHINK_STATE, self::STATE_CREATED );
			$store->set( Priniti_Meta::ITHINK_AWB, $result['awb'] );
			$store->set( Priniti_Meta::ITHINK_COURIER, $result['courier'] );
			$store->set( Priniti_Meta::ITHINK_REFNUM, $result['refnum'] );
			$store->set( Priniti_Meta::ITHINK_TRACKING_URL, $result['tracking_url'] );
			$store->set( Priniti_Meta::ITHINK_CREATED_AT, gmdate( 'c' ) );
			$note = sprintf( 'iThink (%s) shipment created. AWB %s%s.', $this->config->env(), $result['awb'], '' !== $result['courier'] ? ' via ' . $result['courier'] : '' );
			if ( Priniti_IThink_Status::advances( $store->status(), 'packed' ) ) {
				$store->set_status( 'packed', $note );
			} else {
				$store->note( $note );
			}
			$store->save();
			return $result;
		} finally {
			$store->unlock();
		}
	}

	/** Label PDF link for the order's AWB (also kept on the order). */
	public function label( Priniti_IThink_Order_Store $store ): string {
		$url = $this->client->label_url( array( $this->awb( $store ) ) );
		$store->set( Priniti_Meta::ITHINK_LABEL_URL, $url );
		$store->save();
		return $url;
	}

	/** Cancels the order's shipment at iThink. Does not change the WooCommerce order status. */
	public function cancel( Priniti_IThink_Order_Store $store ): void {
		$awb = $this->awb( $store );
		try {
			$results = $this->client->cancel( array( $awb ) );
		} catch ( Priniti_IThink_Error $e ) {
			$store->note( sprintf( 'iThink cancellation of AWB %s %s: %s', $awb, $e->unanswered() ? 'got no answer (check the iThink panel)' : 'failed', $e->getMessage() ) );
			$store->save();
			throw $e;
		}
		if ( ! $results[0]['ok'] ) {
			$message = 'iThink did not cancel the shipment' . ( '' !== $results[0]['remark'] ? ': ' . $results[0]['remark'] : '.' );
			$store->note( "AWB {$awb}: {$message}" );
			$store->save();
			throw new Priniti_IThink_Error( 'api', $message );
		}
		$store->set( Priniti_Meta::ITHINK_STATE, self::STATE_CANCELLED );
		$store->note( sprintf( 'iThink (%s) shipment AWB %s cancelled.', $this->config->env(), $awb ) );
		$store->save();
	}

	/**
	 * Fetches tracking and stores it. With $update_order, also moves the order forward on the journey.
	 *
	 * @return array{status: string, code: string, stage: string|null, attention: bool}
	 */
	public function refresh( Priniti_IThink_Order_Store $store, bool $update_order ): array {
		$awb      = $this->awb( $store );
		$tracking = $this->client->track( array( $awb ) )[ $awb ] ?? null;
		if ( ! $tracking ) {
			throw new Priniti_IThink_Error( 'invalid_response', "iThink returned no tracking for AWB {$awb}." );
		}
		$mapped   = Priniti_IThink_Status::map( $tracking['code'], $tracking['status'] );
		$previous = $store->get( Priniti_Meta::ITHINK_STATUS );
		$store->set( Priniti_Meta::ITHINK_STATUS, $tracking['status'] );
		$store->set( Priniti_Meta::ITHINK_STATUS_CODE, $tracking['code'] );
		$store->set( Priniti_Meta::ITHINK_EXPECTED, $tracking['expected_delivery'] );
		$store->set( Priniti_Meta::ITHINK_SYNCED_AT, gmdate( 'c' ) );
		if ( '' !== $tracking['courier'] && '' === $store->get( Priniti_Meta::ITHINK_COURIER ) ) {
			$store->set( Priniti_Meta::ITHINK_COURIER, $tracking['courier'] );
		}
		if ( $update_order && $previous !== $tracking['status'] ) {
			$note = sprintf( 'iThink tracking for AWB %s: %s (%s)%s', $awb, $tracking['status'], $tracking['code'], $mapped['attention'] ? '. Needs attention.' : '.' );
			if ( Priniti_IThink_Status::advances( $store->status(), $mapped['stage'] ) ) {
				$store->set_status( (string) $mapped['stage'], $note );
			} else {
				$store->note( $note );
			}
		}
		$store->save();
		return array( 'status' => $tracking['status'], 'code' => $tracking['code'] ) + $mapped;
	}

	private function awb( Priniti_IThink_Order_Store $store ): string {
		$awb = $store->get( Priniti_Meta::ITHINK_AWB );
		if ( '' === $awb ) {
			throw new Priniti_IThink_Error( 'validation', 'This order has no iThink shipment.' );
		}
		return $awb;
	}
}
