<?php
declare(strict_types=1);

function test_service( Fake_Transport $t, array $config = array() ): Priniti_IThink_Shipments {
	$c = test_config( $config );
	return new Priniti_IThink_Shipments( $c, new Priniti_IThink_Client( $c, $t ) );
}

test( 'shipments: success stores the AWB, marks Packed and unlocks', function () {
	$t     = new Fake_Transport( array( created_response( '1112223334445' ) ) );
	$store = new Fake_Order_Store();
	$r     = test_service( $t )->create( $store, test_order(), test_package() );
	assert_same( '1112223334445', $r['awb'] );
	assert_same( '1112223334445', $store->get( Priniti_Meta::ITHINK_AWB ) );
	assert_same( 'created', $store->get( Priniti_Meta::ITHINK_STATE ) );
	assert_same( 'staging', $store->get( Priniti_Meta::ITHINK_ENV ) );
	assert_same( 'packed', $store->wc_status );
	assert_same( false, $store->locked );
	assert_true( str_contains( implode( ' ', $store->notes ), 'AWB 1112223334445' ) );
} );

test( 'shipments: a second create is refused without calling iThink', function () {
	$t     = new Fake_Transport( array( created_response() ) );
	$store = new Fake_Order_Store();
	$svc   = test_service( $t );
	$svc->create( $store, test_order(), test_package() );
	assert_ithink_error( 'duplicate', fn () => $svc->create( $store, test_order(), test_package() ) );
	assert_same( 1, count( $t->calls ) );
	assert_true( Priniti_IThink_Shipments::blocks_new( $store ) );
} );

test( 'shipments: a held lock (simultaneous click) is refused', function () {
	$t      = new Fake_Transport();
	$store  = new Fake_Order_Store();
	$store->locked = true;
	assert_ithink_error( 'duplicate', fn () => test_service( $t )->create( $store, test_order(), test_package() ) );
	assert_same( 0, count( $t->calls ) );
} );

test( 'shipments: no answer makes the state uncertain and blocks retries until the panel is checked', function () {
	$t     = new Fake_Transport( array( new Priniti_IThink_Error( 'timeout', 'Operation timed out' ) ) );
	$store = new Fake_Order_Store();
	$svc   = test_service( $t );
	assert_ithink_error( 'timeout', fn () => $svc->create( $store, test_order(), test_package() ) );
	assert_same( 'uncertain', $store->get( Priniti_Meta::ITHINK_STATE ) );
	assert_same( false, $store->locked );
	assert_ithink_error( 'uncertain', fn () => $svc->create( $store, test_order(), test_package() ) );
	assert_same( 1, count( $t->calls ) );

	$t->push( created_response( '5556667778889' ) );
	$svc->create( $store, test_order(), test_package(), true );
	assert_same( '5556667778889', $store->get( Priniti_Meta::ITHINK_AWB ) );
} );

test( 'shipments: an API refusal is recorded as failed and may be retried', function () {
	$t     = new Fake_Transport( array( array( 'status' => 200, 'body' => json_encode( array( 'status' => 'error', 'status_code' => 400, 'html_message' => 'Invalid pincode' ) ) ), created_response() ) );
	$store = new Fake_Order_Store();
	$svc   = test_service( $t );
	assert_ithink_error( 'api', fn () => $svc->create( $store, test_order(), test_package() ) );
	assert_same( 'failed', $store->get( Priniti_Meta::ITHINK_STATE ) );
	assert_same( 'iThink returned an error: Invalid pincode', $store->get( Priniti_Meta::ITHINK_ERROR ) );
	$svc->create( $store, test_order(), test_package() );
	assert_same( 'created', $store->get( Priniti_Meta::ITHINK_STATE ) );
	assert_same( '', $store->get( Priniti_Meta::ITHINK_ERROR ) );
} );

test( 'shipments: authentication failure creates nothing and leaves a retryable state', function () {
	$t     = new Fake_Transport( array( array( 'status' => 403, 'body' => '' ) ) );
	$store = new Fake_Order_Store();
	assert_ithink_error( 'auth', fn () => test_service( $t )->create( $store, test_order(), test_package() ) );
	assert_same( 'failed', $store->get( Priniti_Meta::ITHINK_STATE ) );
	assert_same( '', $store->get( Priniti_Meta::ITHINK_AWB ) );
	assert_same( false, Priniti_IThink_Shipments::blocks_new( $store ) );
} );

test( 'shipments: invalid package data never reaches iThink or changes state', function () {
	$t     = new Fake_Transport();
	$store = new Fake_Order_Store();
	assert_ithink_error( 'validation', fn () => test_service( $t )->create( $store, test_order(), test_package( array( 'weight_g' => 0 ) ) ) );
	assert_same( 0, count( $t->calls ) );
	assert_same( array(), $store->meta );
} );

test( 'shipments: tracking refresh moves the order forward only', function () {
	$track = fn ( string $status, string $code ) => ok_json( array( 'data' => array( '1112223334445' => array( 'current_status' => $status, 'current_status_code' => $code, 'logistic' => 'Delhivery' ) ) ) );
	$t     = new Fake_Transport( array( $track( 'In Transit', 'UD' ), $track( 'Delivered', 'DL' ), $track( 'RTO In Transit', 'RT' ) ) );
	$store = new Fake_Order_Store( 'packed' );
	$store->meta[ Priniti_Meta::ITHINK_AWB ]   = '1112223334445';
	$store->meta[ Priniti_Meta::ITHINK_STATE ] = 'created';
	$svc = test_service( $t );
	$svc->refresh( $store, true );
	assert_same( 'shipped', $store->wc_status );
	$svc->refresh( $store, true );
	assert_same( 'delivered', $store->wc_status );
	$r = $svc->refresh( $store, true );
	assert_same( 'delivered', $store->wc_status, 'never moves backwards' );
	assert_same( true, $r['attention'] );
} );

test( 'shipments: tracking refresh without update_order leaves the status alone', function () {
	$t     = new Fake_Transport( array( ok_json( array( 'data' => array( '1112223334445' => array( 'current_status' => 'Delivered', 'current_status_code' => 'DL' ) ) ) ) ) );
	$store = new Fake_Order_Store( 'packed' );
	$store->meta[ Priniti_Meta::ITHINK_AWB ] = '1112223334445';
	test_service( $t )->refresh( $store, false );
	assert_same( 'packed', $store->wc_status );
	assert_same( 'Delivered', $store->get( Priniti_Meta::ITHINK_STATUS ) );
} );

test( 'shipments: cancel marks the shipment cancelled and keeps the order status', function () {
	$t     = new Fake_Transport( array( ok_json( array( 'data' => array( '1' => array( 'status' => 'Success', 'remark' => '', 'refnum' => 'TEST-1001' ) ) ) ) ) );
	$store = new Fake_Order_Store( 'packed' );
	$store->meta[ Priniti_Meta::ITHINK_AWB ]   = '1112223334445';
	$store->meta[ Priniti_Meta::ITHINK_STATE ] = 'created';
	test_service( $t )->cancel( $store );
	assert_same( 'cancelled', $store->get( Priniti_Meta::ITHINK_STATE ) );
	assert_same( 'packed', $store->wc_status );
} );
