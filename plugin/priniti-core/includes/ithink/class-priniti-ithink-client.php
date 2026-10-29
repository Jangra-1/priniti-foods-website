<?php
/**
 * Server-side client for the iThink Logistics API v3 (docs.ithinklogistics.com/index/3).
 *
 * Every documented call is a POST of JSON {"data": {..., "access_token", "secret_key"}}; a success answer has
 * "status": "success" and "status_code": 200, and errors come back in "html_message". The transport is injected
 * (wp_remote_post on the site, a fake in tests), so this class has no WordPress dependency.
 *
 * Safety rules:
 *   - Calls that only read (pincode, rate, track, label, manifest, warehouse, store) are retried once when
 *     iThink gives no usable answer. Calls that change something (create, cancel) are NEVER retried
 *     automatically: a lost answer may mean iThink already acted, so the caller must check first.
 *   - Weights are sent exactly as given (grams converted to kg without rounding); nothing is rounded silently.
 *   - Log lines and error messages pass through the redactor (no credentials, no customer details).
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

final class Priniti_IThink_Client {
	/** Documented per-request limits. */
	public const MAX_TRACK_AWBS  = 10;
	public const MAX_LABEL_AWBS  = 100;
	public const MAX_CANCEL_AWBS = 100;
	public const MAX_RATE_GRAMS  = 10000;

	/** @var callable(string $url, string $body, int $timeout): array{status: int, body: string} */
	private $transport;

	/** @var callable(string $level, string $message, array $context): void */
	private $logger;

	private Priniti_IThink_Redactor $redactor;

	public function __construct( private Priniti_IThink_Config $config, callable $transport, ?callable $logger = null ) {
		$this->transport = $transport;
		$this->logger    = $logger ?? static function (): void {};
		$this->redactor  = new Priniti_IThink_Redactor( $config->secrets() );
	}

	public function redactor(): Priniti_IThink_Redactor {
		return $this->redactor;
	}

	/* ---- Configuration check ---- */

	/**
	 * Read-only check that the credentials work and the pickup address / store IDs exist on this account.
	 *
	 * @return array{warehouse: array|null, store: array|null}
	 */
	public function verify(): array {
		$out = array( 'warehouse' => null, 'store' => null );
		$pickup = $this->config->pickup_address_id();
		$res    = $this->call( 'warehouse', $pickup ? array( 'warehouse_id' => $pickup ) : array(), true );
		foreach ( (array) ( $res['data'] ?? array() ) as $w ) {
			if ( is_array( $w ) && ( ! $pickup || (int) ( $w['id'] ?? 0 ) === $pickup ) ) {
				$out['warehouse'] = array(
					'id'      => (int) ( $w['id'] ?? 0 ),
					'pincode' => (string) ( $w['pincode'] ?? '' ),
					'city'    => (string) ( $w['city_name'] ?? '' ),
					'status'  => (string) ( $w['status'] ?? '' ),
				);
				break;
			}
		}
		$store = $this->config->store_id();
		if ( $store ) {
			$res = $this->call( 'store', array( 'store_id' => $store ), true );
			foreach ( (array) ( $res['data'] ?? array() ) as $s ) {
				if ( is_array( $s ) && (int) ( $s['id'] ?? 0 ) === $store ) {
					$out['store'] = array(
						'id'       => $store,
						'platform' => (string) ( $s['platform_name'] ?? '' ),
						'url'      => (string) ( $s['store_url'] ?? '' ),
					);
					break;
				}
			}
		}
		return $out;
	}

	/* ---- Serviceability and rates ---- */

	/**
	 * Couriers serving a pincode.
	 *
	 * @return array<string, array{prepaid: bool, cod: bool, pickup: bool}> courier => flags
	 */
	public function check_pincode( string $pincode ): array {
		$pincode = self::pincode( $pincode );
		$res     = $this->call( 'pincode', array( 'pincode' => $pincode ), true );
		$out     = array();
		foreach ( (array) ( $res['data'][ $pincode ] ?? array() ) as $courier => $flags ) {
			if ( is_array( $flags ) ) {
				$out[ (string) $courier ] = array(
					'prepaid' => 'Y' === strtoupper( (string) ( $flags['prepaid'] ?? '' ) ),
					'cod'     => 'Y' === strtoupper( (string) ( $flags['cod'] ?? '' ) ),
					'pickup'  => 'Y' === strtoupper( (string) ( $flags['pickup'] ?? '' ) ),
				);
			}
		}
		return $out;
	}

	/**
	 * Courier rates for one parcel, cheapest first.
	 *
	 * @param array{to_pincode: string, weight_g: int, payment: string, product_mrp: float, length_cm?: float, width_cm?: float, height_cm?: float} $parcel
	 * @return array<int, array{courier: string, service: string, rate: float, tat: string, prepaid: bool, cod: bool}>
	 */
	public function get_rates( array $parcel ): array {
		$from = $this->config->origin_pincode();
		if ( '' === $from ) {
			throw new Priniti_IThink_Error( 'config', 'PRINITI_ITHINK_ORIGIN_PINCODE is not set, so rates cannot be requested.' );
		}
		$grams = (int) ( $parcel['weight_g'] ?? 0 );
		if ( $grams <= 0 ) {
			throw new Priniti_IThink_Error( 'validation', 'A verified parcel weight is required for a rate.' );
		}
		if ( $grams > self::MAX_RATE_GRAMS ) {
			throw new Priniti_IThink_Error( 'validation', 'The rate API accepts parcels up to 10 kg.' );
		}
		$data = array(
			'from_pincode'       => $from,
			'to_pincode'         => self::pincode( (string) ( $parcel['to_pincode'] ?? '' ) ),
			'shipping_weight_kg' => self::grams_to_kg( $grams ),
			'order_type'         => 'forward',
			'payment_method'     => self::payment_label( (string) ( $parcel['payment'] ?? '' ) ),
			'product_mrp'        => self::money( (float) ( $parcel['product_mrp'] ?? 0 ) ),
		);
		foreach ( array( 'length_cm' => 'shipping_length_cms', 'width_cm' => 'shipping_width_cms', 'height_cm' => 'shipping_height_cms' ) as $in => $out ) {
			if ( ! empty( $parcel[ $in ] ) ) {
				$data[ $out ] = self::number( (float) $parcel[ $in ] );
			}
		}
		$res   = $this->call( 'rate', $data, true );
		$rates = array();
		foreach ( (array) ( $res['data'] ?? array() ) as $r ) {
			if ( ! is_array( $r ) || ! is_numeric( $r['rate'] ?? null ) ) {
				continue;
			}
			$rates[] = array(
				'courier' => (string) ( $r['logistic_name'] ?? '' ),
				'service' => (string) ( $r['logistic_service_type'] ?? '' ),
				'rate'    => (float) $r['rate'],
				'tat'     => (string) ( $r['delivery_tat'] ?? '' ),
				'prepaid' => 'Y' === strtoupper( (string) ( $r['prepaid'] ?? '' ) ),
				'cod'     => 'Y' === strtoupper( (string) ( $r['cod'] ?? '' ) ),
			);
		}
		usort( $rates, static fn ( $a, $b ) => $a['rate'] <=> $b['rate'] );
		return $rates;
	}

	/* ---- Shipments ---- */

	/**
	 * Creates one forward shipment (Priniti_IThink_Payload::shipment() builds it). Never retried automatically.
	 *
	 * @return array{awb: string, courier: string, refnum: string, tracking_url: string}
	 */
	public function create_shipment( array $shipment ): array {
		$pickup = $this->config->pickup_address_id();
		if ( ! $pickup ) {
			throw new Priniti_IThink_Error( 'config', 'PRINITI_ITHINK_PICKUP_ADDRESS_ID is not set.' );
		}
		$data = array(
			'shipments'         => array( $shipment ),
			'pickup_address_id' => (string) $pickup,
			'order_type'        => 'forward',
		);
		if ( '' !== $this->config->logistics() ) {
			$data['logistics'] = $this->config->logistics();
		}
		if ( '' !== $this->config->s_type() ) {
			$data['s_type'] = $this->config->s_type();
		}
		$res  = $this->call( 'create', $data, false );
		$item = self::first_item( $res );
		$awb  = trim( (string) ( $item['waybill'] ?? '' ) );
		if ( 'success' !== strtolower( (string) ( $item['status'] ?? '' ) ) || '' === $awb ) {
			throw new Priniti_IThink_Error( 'api', $this->message( 'iThink did not create the shipment', (string) ( $item['remark'] ?? '' ) ) );
		}
		return array(
			'awb'          => $awb,
			'courier'      => (string) ( $item['logistic_name'] ?? '' ),
			'refnum'       => (string) ( $item['refnum'] ?? '' ),
			'tracking_url' => self::https_url( (string) ( $item['tracking_url'] ?? '' ) ),
		);
	}

	/** URL of the shipping label PDF. */
	public function label_url( array $awbs, string $page_size = '' ): string {
		$data = array( 'awb_numbers' => self::awb_list( $awbs, self::MAX_LABEL_AWBS ) );
		if ( '' !== $page_size ) {
			$data['page_size'] = $page_size;
		}
		return $this->file_url( $this->call( 'label', $data, true ) );
	}

	/** URL of the manifest PDF. */
	public function manifest_url( array $awbs ): string {
		return $this->file_url( $this->call( 'manifest', array( 'awb_numbers' => self::awb_list( $awbs, self::MAX_LABEL_AWBS ) ), true ) );
	}

	/**
	 * Cancels shipments. Never retried automatically.
	 *
	 * @return array<int, array{ok: bool, refnum: string, remark: string}>
	 */
	public function cancel( array $awbs ): array {
		$res = $this->call( 'cancel', array( 'awb_numbers' => self::awb_list( $awbs, self::MAX_CANCEL_AWBS ) ), false );
		$out = array();
		foreach ( (array) ( $res['data'] ?? array() ) as $item ) {
			if ( is_array( $item ) ) {
				$out[] = array(
					'ok'     => 'success' === strtolower( (string) ( $item['status'] ?? '' ) ),
					'refnum' => (string) ( $item['refnum'] ?? '' ),
					'remark' => $this->redactor->text( (string) ( $item['remark'] ?? '' ) ),
				);
			}
		}
		if ( ! $out ) {
			throw new Priniti_IThink_Error( 'invalid_response', 'iThink did not report a result for the cancellation.' );
		}
		return $out;
	}

	/**
	 * Tracking for up to 10 AWBs.
	 *
	 * @return array<string, array{status: string, code: string, courier: string, expected_delivery: string, last_scan: string, last_scan_at: string}>
	 */
	public function track( array $awbs ): array {
		$res = $this->call( 'track', array( 'awb_number_list' => self::awb_list( $awbs, self::MAX_TRACK_AWBS ) ), true );
		$out = array();
		foreach ( (array) ( $res['data'] ?? array() ) as $awb => $t ) {
			if ( ! is_array( $t ) ) {
				continue;
			}
			$last              = is_array( $t['last_scan_details'] ?? null ) ? $t['last_scan_details'] : array();
			$out[ (string) $awb ] = array(
				'status'            => (string) ( $t['current_status'] ?? '' ),
				'code'              => strtoupper( (string) ( $t['current_status_code'] ?? '' ) ),
				'courier'           => (string) ( $t['logistic'] ?? '' ),
				'expected_delivery' => (string) ( $t['expected_delivery_date'] ?? '' ),
				'last_scan'         => (string) ( $last['scan_location'] ?? '' ),
				'last_scan_at'      => (string) ( $last['status_date_time'] ?? '' ),
			);
		}
		return $out;
	}

	/* ---- Transport ---- */

	/**
	 * One API call. Returns the decoded success answer or throws Priniti_IThink_Error.
	 */
	private function call( string $endpoint, array $data, bool $retry_unanswered ): array {
		if ( $problems = $this->config->problems() ) {
			throw new Priniti_IThink_Error( 'config', 'iThink is not configured: ' . implode( ' ', $problems ) );
		}
		$url  = $this->config->url( $endpoint );
		$body = json_encode( array( 'data' => $data + $this->config->credentials() ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		if ( false === $body ) {
			throw new Priniti_IThink_Error( 'validation', 'The request could not be encoded as JSON.' );
		}
		if ( $this->config->debug() ) {
			$this->log( 'debug', "request {$endpoint}", array( 'env' => $this->config->env(), 'data' => $this->redactor->data( $data ) ) );
		}

		$attempts = $retry_unanswered ? 2 : 1;
		for ( $attempt = 1; ; $attempt++ ) {
			try {
				return $this->parse( $endpoint, $this->send( $url, $body ) );
			} catch ( Priniti_IThink_Error $e ) {
				$this->log( 'error', "{$endpoint} failed ({$e->kind}, attempt {$attempt}/{$attempts}): {$e->getMessage()}", array( 'env' => $this->config->env() ) );
				if ( $attempt >= $attempts || ! $e->unanswered() ) {
					throw $e;
				}
			}
		}
	}

	/** @return array{status: int, body: string} */
	private function send( string $url, string $body ): array {
		try {
			$res = ( $this->transport )( $url, $body, $this->config->timeout() );
		} catch ( Priniti_IThink_Error $e ) {
			throw new Priniti_IThink_Error( $e->kind, $this->redactor->text( $e->getMessage() ) );
		} catch ( Throwable $e ) {
			throw new Priniti_IThink_Error( 'network', $this->message( 'Could not reach iThink', $e->getMessage() ) );
		}
		return array(
			'status' => (int) ( $res['status'] ?? 0 ),
			'body'   => (string) ( $res['body'] ?? '' ),
		);
	}

	private function parse( string $endpoint, array $res ): array {
		$status = $res['status'];
		$json   = json_decode( $res['body'], true );
		$text   = is_array( $json ) ? (string) ( $json['html_message'] ?? $json['message'] ?? '' ) : '';

		if ( 401 === $status || 403 === $status ) {
			throw new Priniti_IThink_Error( 'auth', $this->message( "iThink rejected the credentials (HTTP {$status})", $text ) );
		}
		if ( $status >= 500 ) {
			throw new Priniti_IThink_Error( 'server', $this->message( "iThink returned HTTP {$status}", $text ) );
		}
		if ( 200 !== $status ) {
			throw new Priniti_IThink_Error( 'http', $this->message( "iThink returned HTTP {$status}", $text ) );
		}
		if ( ! is_array( $json ) ) {
			throw new Priniti_IThink_Error( 'invalid_response', "iThink's answer to {$endpoint} was not valid JSON." );
		}
		$ok = 'success' === strtolower( (string) ( $json['status'] ?? '' ) ) && 200 === (int) ( $json['status_code'] ?? 0 );
		if ( ! $ok ) {
			// The docs do not list error codes; credentials problems are recognised by their message.
			$kind = preg_match( '/access[\s_-]*token|secret[\s_-]*key|unauthori[sz]ed|authenticat|invalid (api )?key/i', $text ) ? 'auth' : 'api';
			throw new Priniti_IThink_Error( $kind, $this->message( 'iThink returned an error', $text ) );
		}
		return $json;
	}

	private function file_url( array $res ): string {
		$url = self::https_url( (string) ( $res['file_name'] ?? '' ) );
		if ( '' === $url ) {
			throw new Priniti_IThink_Error( 'invalid_response', 'iThink did not return a file link.' );
		}
		return $url;
	}

	private function message( string $prefix, string $detail ): string {
		$detail = trim( (string) preg_replace( '/\s+/', ' ', strip_tags( $detail ) ) );
		$detail = $this->redactor->text( $detail );
		if ( strlen( $detail ) > 300 ) {
			$detail = substr( $detail, 0, 300 ) . '…';
		}
		return '' === $detail ? "{$prefix}." : "{$prefix}: {$detail}";
	}

	private function log( string $level, string $message, array $context = array() ): void {
		( $this->logger )( $level, $this->redactor->text( $message ), $this->redactor->data( $context ) );
	}

	/* ---- Value helpers (pure, public for the payload builder and tests) ---- */

	public static function pincode( string $pin ): string {
		$pin = trim( $pin );
		if ( ! preg_match( '/^[1-9]\d{5}$/', $pin ) ) {
			throw new Priniti_IThink_Error( 'validation', 'Enter a valid 6-digit pincode.' );
		}
		return $pin;
	}

	/** Grams to a kg string without rounding: 450 => "0.45", 1000 => "1". */
	public static function grams_to_kg( int $grams ): string {
		return self::number( $grams / 1000 );
	}

	/** Decimal string without float noise or trailing zeros (up to 3 decimals, which grams need). */
	public static function number( float $n ): string {
		return rtrim( rtrim( number_format( $n, 3, '.', '' ), '0' ), '.' );
	}

	public static function money( float $n ): string {
		return number_format( round( $n, 2 ), 2, '.', '' );
	}

	/** 'cod' => "COD", anything else => "Prepaid" (iThink treats a missing payment mode as COD, so it is always sent). */
	public static function payment_label( string $payment ): string {
		return 'cod' === strtolower( $payment ) ? 'COD' : 'Prepaid';
	}

	public static function https_url( string $url ): string {
		$url = trim( $url );
		if ( str_starts_with( $url, 'http://' ) ) {
			$url = 'https://' . substr( $url, 7 );
		}
		return preg_match( '#^https://[^\s"<>]+$#', $url ) ? $url : '';
	}

	private static function awb_list( array $awbs, int $max ): string {
		$clean = array_values( array_unique( array_filter( array_map( static fn ( $a ) => preg_replace( '/[^A-Za-z0-9]/', '', (string) $a ), $awbs ) ) ) );
		if ( ! $clean ) {
			throw new Priniti_IThink_Error( 'validation', 'No AWB number given.' );
		}
		if ( count( $clean ) > $max ) {
			throw new Priniti_IThink_Error( 'validation', "At most {$max} AWB numbers per request." );
		}
		return implode( ',', $clean );
	}

	private static function first_item( array $res ): array {
		$data = (array) ( $res['data'] ?? array() );
		$item = $data['1'] ?? $data[1] ?? reset( $data );
		if ( ! is_array( $item ) ) {
			throw new Priniti_IThink_Error( 'invalid_response', 'iThink did not report a result for the shipment.' );
		}
		return $item;
	}
}
