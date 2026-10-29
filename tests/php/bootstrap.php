<?php
/**
 * Test bootstrap: loads the WordPress-free iThink classes, a tiny test runner and the fakes.
 */

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$priniti_plugin = dirname( __DIR__, 2 ) . '/plugin/priniti-core/includes/';
require $priniti_plugin . 'meta-keys.php';
foreach ( array( 'error', 'config', 'redactor', 'client', 'payload', 'status', 'shipments' ) as $priniti_class ) {
	require $priniti_plugin . "ithink/class-priniti-ithink-{$priniti_class}.php";
}

/* ---- Runner ---- */

$GLOBALS['priniti_tests'] = array();

function test( string $name, callable $fn ): void {
	$GLOBALS['priniti_tests'][ $name ] = $fn;
}

final class Priniti_Test_Failure extends Exception {}

function assert_same( $expected, $actual, string $message = '' ): void {
	if ( $expected !== $actual ) {
		throw new Priniti_Test_Failure( ( $message ? "{$message}: " : '' ) . 'expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) );
	}
}

function assert_true( $value, string $message = 'expected true' ): void {
	assert_same( true, (bool) $value, $message );
}

function assert_not_contains( string $needle, string $haystack, string $message = '' ): void {
	if ( '' !== $needle && str_contains( $haystack, $needle ) ) {
		throw new Priniti_Test_Failure( ( $message ? "{$message}: " : '' ) . "unexpected \"{$needle}\" found" );
	}
}

/** Runs $fn, expects a Priniti_IThink_Error of $kind, returns it. */
function assert_ithink_error( string $kind, callable $fn ): Priniti_IThink_Error {
	try {
		$fn();
	} catch ( Priniti_IThink_Error $e ) {
		assert_same( $kind, $e->kind, 'error kind (' . $e->getMessage() . ')' );
		return $e;
	}
	throw new Priniti_Test_Failure( "expected a \"{$kind}\" error, none thrown" );
}

function priniti_test_run(): int {
	$failed = 0;
	foreach ( $GLOBALS['priniti_tests'] as $name => $fn ) {
		try {
			$fn();
			echo "ok    {$name}\n";
		} catch ( Throwable $e ) {
			++$failed;
			echo "FAIL  {$name}\n      " . get_class( $e ) . ': ' . $e->getMessage() . "\n";
		}
	}
	$total = count( $GLOBALS['priniti_tests'] );
	echo "\n" . ( $total - $failed ) . "/{$total} tests passed\n";
	return $failed ? 1 : 0;
}

/* ---- Fakes ---- */

const TEST_TOKEN  = 'tok_TEST_7f3a9c';
const TEST_SECRET = 'sec_TEST_51be02';

function test_config( array $overrides = array() ): Priniti_IThink_Config {
	return new Priniti_IThink_Config(
		$overrides + array(
			'enabled'           => true,
			'env'               => 'staging',
			'access_token'      => TEST_TOKEN,
			'secret_key'        => TEST_SECRET,
			'store_id'          => 34463,
			'pickup_address_id' => 1293,
			'return_address_id' => 1293,
			'origin_pincode'    => '122001',
			'weight_unit'       => 'kg',
		)
	);
}

/** Scripted transport: each queued item is a response array or a Throwable to throw. Records every call. */
final class Fake_Transport {
	public array $calls = array();

	public function __construct( private array $queue = array() ) {}

	public function push( $response ): self {
		$this->queue[] = $response;
		return $this;
	}

	public function __invoke( string $url, string $body, int $timeout ): array {
		$this->calls[] = array( 'url' => $url, 'body' => json_decode( $body, true ), 'raw' => $body, 'timeout' => $timeout );
		$next = array_shift( $this->queue );
		if ( null === $next ) {
			throw new RuntimeException( 'Fake transport: no response queued' );
		}
		if ( $next instanceof Throwable ) {
			throw $next;
		}
		return $next;
	}
}

function ok_json( array $payload ): array {
	return array( 'status' => 200, 'body' => json_encode( $payload + array( 'status' => 'success', 'status_code' => 200 ) ) );
}

/** Collects log lines so tests can check nothing sensitive is written. */
final class Fake_Logger {
	public array $lines = array();

	public function __invoke( string $level, string $message, array $context ): void {
		$this->lines[] = "{$level} {$message} " . json_encode( $context );
	}

	public function all(): string {
		return implode( "\n", $this->lines );
	}
}

final class Fake_Order_Store implements Priniti_IThink_Order_Store {
	public array $meta   = array();
	public array $notes  = array();
	public int $saves    = 0;
	public bool $locked  = false;

	public function __construct( public string $wc_status = 'processing' ) {}

	public function get( string $key ): string {
		return $this->meta[ $key ] ?? '';
	}

	public function set( string $key, string $value ): void {
		if ( '' === $value ) {
			unset( $this->meta[ $key ] );
		} else {
			$this->meta[ $key ] = $value;
		}
	}

	public function note( string $text ): void {
		$this->notes[] = $text;
	}

	public function status(): string {
		return $this->wc_status;
	}

	public function set_status( string $status, string $note ): void {
		$this->wc_status = $status;
		$this->notes[]   = $note;
	}

	public function save(): void {
		++$this->saves;
	}

	public function lock(): bool {
		if ( $this->locked ) {
			return false;
		}
		return $this->locked = true;
	}

	public function unlock(): void {
		$this->locked = false;
	}
}

function test_order( array $overrides = array() ): array {
	return $overrides + array(
		'number'         => 'TEST-1001',
		'date'           => new DateTimeImmutable( '2026-10-29 10:00:00' ),
		'total'          => 528.0,
		'payment'        => 'prepaid',
		'name'           => 'Test Customer',
		'address_1'      => '12 Test Street',
		'address_2'      => 'Near Test Park',
		'city'           => 'Gurugram',
		'state'          => 'Haryana',
		'pincode'        => '122002',
		'phone'          => '+91 98765 43210',
		'email'          => 'test.customer@example.com',
		'shipping_total' => 60.0,
		'discount_total' => 0.0,
		'items'          => array(
			array( 'name' => 'Aloo Bhujia 400 g', 'sku' => 'ALB-400', 'qty' => 2, 'unit_price' => 105.6 ),
			array( 'name' => 'Soan Papdi 250 g', 'sku' => '', 'qty' => 1, 'unit_price' => 256.8 ),
		),
	);
}

function test_package( array $overrides = array() ): array {
	return $overrides + array( 'weight_g' => 1450, 'length_cm' => 30.0, 'width_cm' => 20.0, 'height_cm' => 15.0 );
}

function created_response( string $awb = '1234567890123' ): array {
	return ok_json(
		array(
			'html_message' => '',
			'data'         => array( '1' => array( 'status' => 'Success', 'remark' => '', 'waybill' => $awb, 'refnum' => 'TEST-1001', 'logistic_name' => 'delhivery', 'tracking_url' => 'http://track.example/awb' ) ),
		)
	);
}
