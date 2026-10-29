<?php
/**
 * iThink Logistics configuration, read from wp-config.php constants only (docs/ITHINK.md lists them).
 *
 * The module is OFF unless PRINITI_ITHINK_ENABLED is true. Credentials live only in wp-config.php on the server:
 * never in Git, the database, JavaScript, HTML, logs or error messages. Nothing here ever returns or prints them;
 * status() reports only whether each value is set.
 *
 * Staging (pre-alpha.ithinklogistics.com) is the default. Production needs PRINITI_ITHINK_ENV = 'production' AND
 * PRINITI_ITHINK_ALLOW_PRODUCTION = true, so a copied staging config can never reach production by accident.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

final class Priniti_IThink_Config {
	/** Documented hosts (API v3). Track's production host differs from the others in the docs: confirm with iThink. */
	private const HOSTS = array(
		'staging'    => array(
			'default' => 'https://pre-alpha.ithinklogistics.com',
			'track'   => 'https://pre-alpha.ithinklogistics.com',
		),
		'production' => array(
			'default' => 'https://my.ithinklogistics.com',
			'track'   => 'https://api.ithinklogistics.com',
		),
	);

	/** Endpoint paths, all POST with a JSON body {"data": {...}} (docs.ithinklogistics.com/index/3). */
	public const PATHS = array(
		'pincode'   => '/api_v3/pincode/check.json',
		'rate'      => '/api_v3/rate/check.json',
		'create'    => '/api_v3/order/add.json',
		'label'     => '/api_v3/shipping/label.json',
		'manifest'  => '/api_v3/shipping/manifest.json',
		'cancel'    => '/api_v3/order/cancel.json',
		'track'     => '/api_v3/order/track.json',
		'warehouse' => '/api_v3/warehouse/get.json',
		'store'     => '/api_v3/store/get.json',
	);

	/** Constant name => config key. */
	public const CONSTANTS = array(
		'PRINITI_ITHINK_ENABLED'            => 'enabled',
		'PRINITI_ITHINK_ENV'                => 'env',
		'PRINITI_ITHINK_ALLOW_PRODUCTION'   => 'allow_production',
		'PRINITI_ITHINK_ACCESS_TOKEN'       => 'access_token',
		'PRINITI_ITHINK_SECRET_KEY'         => 'secret_key',
		'PRINITI_ITHINK_STORE_ID'           => 'store_id',
		'PRINITI_ITHINK_PICKUP_ADDRESS_ID'  => 'pickup_address_id',
		'PRINITI_ITHINK_RETURN_ADDRESS_ID'  => 'return_address_id',
		'PRINITI_ITHINK_ORIGIN_PINCODE'     => 'origin_pincode',
		'PRINITI_ITHINK_WEIGHT_UNIT'        => 'weight_unit',
		'PRINITI_ITHINK_LOGISTICS'          => 'logistics',
		'PRINITI_ITHINK_S_TYPE'             => 's_type',
		'PRINITI_ITHINK_DEFAULT_BOX_CM'     => 'default_box',
		'PRINITI_ITHINK_TIMEOUT'            => 'timeout',
		'PRINITI_ITHINK_DEBUG'              => 'debug',
	);

	private array $values;

	public function __construct( array $values = array() ) {
		$this->values = $values;
	}

	public static function from_constants(): self {
		$values = array();
		foreach ( self::CONSTANTS as $constant => $key ) {
			if ( defined( $constant ) ) {
				$values[ $key ] = constant( $constant );
			}
		}
		return new self( $values );
	}

	public function enabled(): bool {
		return true === ( $this->values['enabled'] ?? false );
	}

	/** 'staging' or 'production'. Anything else counts as staging. */
	public function env(): string {
		return 'production' === ( $this->values['env'] ?? 'staging' ) ? 'production' : 'staging';
	}

	public function is_production(): bool {
		return 'production' === $this->env();
	}

	public function url( string $endpoint ): string {
		if ( ! isset( self::PATHS[ $endpoint ] ) ) {
			throw new Priniti_IThink_Error( 'config', "Unknown iThink endpoint \"{$endpoint}\"." );
		}
		$hosts = self::HOSTS[ $this->env() ];
		return ( 'track' === $endpoint ? $hosts['track'] : $hosts['default'] ) . self::PATHS[ $endpoint ];
	}

	/** @return array{access_token: string, secret_key: string} Only for the client's request body. */
	public function credentials(): array {
		return array(
			'access_token' => $this->string( 'access_token' ),
			'secret_key'   => $this->string( 'secret_key' ),
		);
	}

	/** Values to scrub from any text before it is logged or shown. */
	public function secrets(): array {
		return array_values( array_filter( $this->credentials(), static fn ( $v ) => strlen( $v ) >= 4 ) );
	}

	public function store_id(): ?int {
		return $this->positive_int( 'store_id' );
	}

	public function pickup_address_id(): ?int {
		return $this->positive_int( 'pickup_address_id' );
	}

	public function return_address_id(): ?int {
		return $this->positive_int( 'return_address_id' );
	}

	public function origin_pincode(): string {
		$pin = $this->string( 'origin_pincode' );
		return preg_match( '/^[1-9]\d{5}$/', $pin ) ? $pin : '';
	}

	/** Unit of the shipment `weight` field: 'kg' or 'g'. Empty until confirmed with iThink (the docs disagree). */
	public function weight_unit(): string {
		$unit = strtolower( $this->string( 'weight_unit' ) );
		return in_array( $unit, array( 'kg', 'g' ), true ) ? $unit : '';
	}

	public function logistics(): string {
		return strtolower( $this->string( 'logistics' ) );
	}

	public function s_type(): string {
		return strtolower( $this->string( 's_type' ) );
	}

	/**
	 * Default box from PRINITI_ITHINK_DEFAULT_BOX_CM ("L x W x H" in cm, e.g. "30x20x15"), or null when unset or invalid.
	 *
	 * @return array{length_cm: float, width_cm: float, height_cm: float}|null
	 */
	public function default_box(): ?array {
		if ( ! preg_match( '/^\s*(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)\s*$/iu', $this->string( 'default_box' ), $m ) ) {
			return null;
		}
		$box = array( 'length_cm' => (float) $m[1], 'width_cm' => (float) $m[2], 'height_cm' => (float) $m[3] );
		return min( $box ) > 0 ? $box : null;
	}

	public function timeout(): int {
		$t = (int) ( $this->values['timeout'] ?? 15 );
		return max( 3, min( 60, $t ) );
	}

	public function debug(): bool {
		return true === ( $this->values['debug'] ?? false );
	}

	/**
	 * Problems that stop API calls. Empty = ready. Names constants only, never values.
	 *
	 * @return string[]
	 */
	public function problems(): array {
		$problems = array();
		if ( ! $this->enabled() ) {
			$problems[] = 'PRINITI_ITHINK_ENABLED is not true (the integration is off).';
		}
		if ( $this->is_production() && true !== ( $this->values['allow_production'] ?? false ) ) {
			$problems[] = 'PRINITI_ITHINK_ENV is "production" but PRINITI_ITHINK_ALLOW_PRODUCTION is not true.';
		}
		if ( '' === $this->string( 'access_token' ) ) {
			$problems[] = 'PRINITI_ITHINK_ACCESS_TOKEN is not set.';
		}
		if ( '' === $this->string( 'secret_key' ) ) {
			$problems[] = 'PRINITI_ITHINK_SECRET_KEY is not set.';
		}
		return $problems;
	}

	/**
	 * Extra problems that stop shipment creation (on top of problems()).
	 *
	 * @return string[]
	 */
	public function shipment_problems(): array {
		$problems = array();
		if ( ! $this->pickup_address_id() ) {
			$problems[] = 'PRINITI_ITHINK_PICKUP_ADDRESS_ID is not set.';
		}
		if ( ! $this->return_address_id() ) {
			$problems[] = 'PRINITI_ITHINK_RETURN_ADDRESS_ID is not set.';
		}
		if ( '' === $this->weight_unit() ) {
			$problems[] = 'PRINITI_ITHINK_WEIGHT_UNIT is not "kg" or "g" (confirm the unit with iThink first).';
		}
		return $problems;
	}

	/**
	 * Read-only overview for the settings screen: which values are set. Never includes credential values.
	 *
	 * @return array<int, array{name: string, state: string}>
	 */
	public function status(): array {
		$show = array(
			'PRINITI_ITHINK_ENABLED'           => $this->enabled() ? 'true' : 'false (off)',
			'PRINITI_ITHINK_ENV'               => $this->env(),
			'PRINITI_ITHINK_ACCESS_TOKEN'      => '' !== $this->string( 'access_token' ) ? 'set' : 'missing',
			'PRINITI_ITHINK_SECRET_KEY'        => '' !== $this->string( 'secret_key' ) ? 'set' : 'missing',
			'PRINITI_ITHINK_STORE_ID'          => $this->store_id() ? (string) $this->store_id() : 'not set',
			'PRINITI_ITHINK_PICKUP_ADDRESS_ID' => $this->pickup_address_id() ? (string) $this->pickup_address_id() : 'missing',
			'PRINITI_ITHINK_RETURN_ADDRESS_ID' => $this->return_address_id() ? (string) $this->return_address_id() : 'missing',
			'PRINITI_ITHINK_ORIGIN_PINCODE'    => '' !== $this->origin_pincode() ? $this->origin_pincode() : 'missing (needed for rates)',
			'PRINITI_ITHINK_WEIGHT_UNIT'       => '' !== $this->weight_unit() ? $this->weight_unit() : 'missing (needed for shipments)',
			'PRINITI_ITHINK_LOGISTICS'         => '' !== $this->logistics() ? $this->logistics() : 'not set (iThink assigns)',
			'PRINITI_ITHINK_S_TYPE'            => '' !== $this->s_type() ? $this->s_type() : 'not set',
			'PRINITI_ITHINK_DEFAULT_BOX_CM'    => $this->default_box() ? implode( ' x ', $this->default_box() ) . ' cm' : 'not set',
		);
		$out = array();
		foreach ( $show as $name => $state ) {
			$out[] = array( 'name' => $name, 'state' => $state );
		}
		return $out;
	}

	private function string( string $key ): string {
		$v = $this->values[ $key ] ?? '';
		return is_scalar( $v ) ? trim( (string) $v ) : '';
	}

	private function positive_int( string $key ): ?int {
		$v = $this->values[ $key ] ?? null;
		return is_numeric( $v ) && (int) $v > 0 && (string) (int) $v === trim( (string) $v ) ? (int) $v : null;
	}
}
