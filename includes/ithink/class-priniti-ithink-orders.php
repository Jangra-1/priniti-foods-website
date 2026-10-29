<?php
/**
 * WooCommerce side of the iThink module: order storage (HPOS-safe, through WC_Order meta), the order data and
 * package the payload needs, verified shipping weights, the HTTP transport and the logger.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

/** Order meta storage + journey status + lock for one WC_Order. */
final class Priniti_IThink_WC_Order_Store implements Priniti_IThink_Order_Store {
	private const LOCK_TTL = 300;

	public function __construct( private WC_Order $order ) {}

	public function get( string $key ): string {
		return (string) $this->order->get_meta( $key, true );
	}

	public function set( string $key, string $value ): void {
		if ( '' === $value ) {
			$this->order->delete_meta_data( $key );
		} else {
			$this->order->update_meta_data( $key, $value );
		}
	}

	public function note( string $text ): void {
		$this->order->add_order_note( $text, 0, true );
	}

	public function status(): string {
		return $this->order->get_status();
	}

	public function set_status( string $status, string $note ): void {
		$this->order->set_status( $status, $note, true );
	}

	public function save(): void {
		$this->order->save();
	}

	/** add_option() inserts a unique row, so only one request can hold the lock. Stale locks expire. */
	public function lock(): bool {
		$name = $this->lock_name();
		if ( add_option( $name, (string) time(), '', false ) ) {
			return true;
		}
		if ( (int) get_option( $name ) < time() - self::LOCK_TTL ) {
			delete_option( $name );
			return add_option( $name, (string) time(), '', false );
		}
		return false;
	}

	public function unlock(): void {
		delete_option( $this->lock_name() );
	}

	private function lock_name(): string {
		return 'priniti_ithink_lock_' . $this->order->get_id();
	}
}

final class Priniti_IThink {
	private static ?Priniti_IThink_Config $config = null;

	public static function config(): Priniti_IThink_Config {
		return self::$config ??= Priniti_IThink_Config::from_constants();
	}

	/** True when PRINITI_ITHINK_ENABLED is true (credentials may still be missing: see config()->problems()). */
	public static function enabled(): bool {
		return self::config()->enabled();
	}

	public static function client(): Priniti_IThink_Client {
		return new Priniti_IThink_Client( self::config(), array( self::class, 'transport' ), array( self::class, 'log' ) );
	}

	public static function shipments(): Priniti_IThink_Shipments {
		return new Priniti_IThink_Shipments( self::config(), self::client() );
	}

	/**
	 * wp_remote_post transport. JSON body, no redirects (a redirect could resend the credentials elsewhere).
	 *
	 * @return array{status: int, body: string}
	 */
	public static function transport( string $url, string $body, int $timeout ): array {
		$res = wp_remote_post(
			$url,
			array(
				'timeout'     => $timeout,
				'redirection' => 0,
				'headers'     => array( 'Content-Type' => 'application/json', 'Accept' => 'application/json' ),
				'body'        => $body,
			)
		);
		if ( is_wp_error( $res ) ) {
			$message = $res->get_error_message();
			throw new Priniti_IThink_Error( preg_match( '/timed? ?out/i', $message ) ? 'timeout' : 'network', 'Could not reach iThink: ' . $message );
		}
		return array(
			'status' => (int) wp_remote_retrieve_response_code( $res ),
			'body'   => (string) wp_remote_retrieve_body( $res ),
		);
	}

	/** WooCommerce > Status > Logs, source "priniti-ithink". Messages and context arrive already redacted. */
	public static function log( string $level, string $message, array $context = array() ): void {
		if ( ! function_exists( 'wc_get_logger' ) ) {
			return;
		}
		$line = $message . ( $context ? ' ' . wp_json_encode( $context ) : '' );
		wc_get_logger()->log( $level, $line, array( 'source' => 'priniti-ithink' ) );
	}

	/* ---- Shipping data ---- */

	/** Verified packed weight (g) of one unit of a product or variation, or null. Never derived from net weight. */
	public static function unit_weight( $product ): ?int {
		if ( ! $product instanceof WC_Product ) {
			return null;
		}
		$grams = (int) $product->get_meta( Priniti_Meta::SHIP_WEIGHT_G, true );
		return $grams > 0 ? $grams : null;
	}

	/**
	 * Parcel suggestion from verified unit weights: weight is the sum when every line has one, otherwise null
	 * with the lines that are missing. Box size comes from PRINITI_ITHINK_DEFAULT_BOX_CM (if set).
	 *
	 * @param array<int, array{product: WC_Product|null, qty: int, name: string}> $lines
	 * @return array{weight_g: int|null, missing: string[], box: array|null}
	 */
	public static function parcel( array $lines ): array {
		$total   = 0;
		$missing = array();
		foreach ( $lines as $line ) {
			$unit = self::unit_weight( $line['product'] );
			if ( null === $unit ) {
				$missing[] = $line['name'];
			} else {
				$total += $unit * max( 1, (int) $line['qty'] );
			}
		}
		return array(
			'weight_g' => $missing || ! $lines ? null : $total,
			'missing'  => array_values( array_unique( $missing ) ),
			'box'      => self::config()->default_box(),
		);
	}

	/** @return array<int, array{product: WC_Product|null, qty: int, name: string}> */
	public static function order_lines( WC_Order $order ): array {
		$lines = array();
		foreach ( $order->get_items() as $item ) {
			if ( $item instanceof WC_Order_Item_Product ) {
				$product = $item->get_product();
				$lines[] = array(
					'product' => $product ?: null,
					'qty'     => (int) $item->get_quantity(),
					'name'    => $item->get_name(),
				);
			}
		}
		return $lines;
	}

	/** The plain order array Priniti_IThink_Payload::shipment() takes. The billing address is the delivery address. */
	public static function order_data( WC_Order $order ): array {
		$states = WC()->countries ? WC()->countries->get_states( 'IN' ) : array();
		$code   = (string) $order->get_billing_state();
		$items  = array();
		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}
			$qty     = max( 1, (int) $item->get_quantity() );
			$product = $item->get_product();
			$items[] = array(
				'name'       => $item->get_name(),
				'sku'        => $product ? (string) $product->get_sku() : '',
				'qty'        => $qty,
				// Price actually charged per unit, so the 12% multi-pack discount carries through.
				'unit_price' => ( (float) $item->get_total() + (float) $item->get_total_tax() ) / $qty,
			);
		}
		$created = $order->get_date_created();
		return array(
			'number'         => (string) $order->get_order_number(),
			'date'           => $created ? new DateTimeImmutable( '@' . $created->getTimestamp() ) : null,
			'total'          => (float) $order->get_total(),
			'payment'        => 'cod' === $order->get_payment_method() ? 'cod' : 'prepaid',
			'name'           => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
			'address_1'      => (string) $order->get_billing_address_1(),
			'address_2'      => (string) $order->get_billing_address_2(),
			'city'           => (string) $order->get_billing_city(),
			// WooCommerce stores the state code (e.g. "HR"); iThink gets the state name.
			'state'          => html_entity_decode( (string) ( $states[ $code ] ?? $code ), ENT_QUOTES ),
			'pincode'        => (string) $order->get_billing_postcode(),
			'phone'          => (string) $order->get_billing_phone(),
			'email'          => (string) $order->get_billing_email(),
			'shipping_total' => (float) $order->get_shipping_total() + (float) $order->get_shipping_tax(),
			'discount_total' => (float) $order->get_discount_total(),
			'items'          => $items,
		);
	}
}
