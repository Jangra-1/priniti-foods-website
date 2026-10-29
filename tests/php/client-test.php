<?php
declare(strict_types=1);

test( 'client: refuses to call when not configured, without touching the network', function () {
	$t = new Fake_Transport();
	$c = new Priniti_IThink_Client( test_config( array( 'enabled' => false ) ), $t );
	assert_ithink_error( 'config', fn () => $c->check_pincode( '122002' ) );
	assert_same( 0, count( $t->calls ) );
} );

test( 'client: pincode check sends documented body and parses courier flags', function () {
	$t = new Fake_Transport( array( ok_json( array( 'data' => array( '122002' => array( 'delhivery' => array( 'prepaid' => 'Y', 'cod' => 'N', 'pickup' => 'Y' ) ) ) ) ) ) );
	$c = new Priniti_IThink_Client( test_config(), $t );
	assert_same( array( 'delhivery' => array( 'prepaid' => true, 'cod' => false, 'pickup' => true ) ), $c->check_pincode( '122002' ) );
	assert_same( 'https://pre-alpha.ithinklogistics.com/api_v3/pincode/check.json', $t->calls[0]['url'] );
	assert_same( array( 'pincode' => '122002', 'access_token' => TEST_TOKEN, 'secret_key' => TEST_SECRET ), $t->calls[0]['body']['data'] );
	assert_ithink_error( 'validation', fn () => $c->check_pincode( '12345' ) );
} );

test( 'client: rates keep fractional kg exactly and sort cheapest first', function () {
	$t = new Fake_Transport(
		array(
			ok_json(
				array(
					'data' => array(
						array( 'logistic_name' => 'Xpressbees', 'logistic_service_type' => 'Surface', 'rate' => 153.4, 'delivery_tat' => '3', 'prepaid' => 'Y', 'cod' => 'Y' ),
						array( 'logistic_name' => 'Delhivery', 'logistic_service_type' => 'Surface', 'rate' => '98.5', 'delivery_tat' => '4', 'prepaid' => 'Y', 'cod' => 'N' ),
					),
				)
			),
		)
	);
	$c     = new Priniti_IThink_Client( test_config(), $t );
	$rates = $c->get_rates( array( 'to_pincode' => '400061', 'weight_g' => 450, 'payment' => 'prepaid', 'product_mrp' => 528, 'length_cm' => 30.0, 'width_cm' => 20.0, 'height_cm' => 15.0 ) );
	$data  = $t->calls[0]['body']['data'];
	assert_same( '0.45', $data['shipping_weight_kg'], 'weight sent unrounded' );
	assert_same( '122001', $data['from_pincode'] );
	assert_same( 'Prepaid', $data['payment_method'] );
	assert_same( '30', $data['shipping_length_cms'] );
	assert_same( 'Delhivery', $rates[0]['courier'] );
	assert_same( 98.5, $rates[0]['rate'] );
} );

test( 'client: rate request rejects missing or over-limit weight', function () {
	$c = new Priniti_IThink_Client( test_config(), new Fake_Transport() );
	assert_ithink_error( 'validation', fn () => $c->get_rates( array( 'to_pincode' => '400061', 'weight_g' => 0, 'payment' => 'prepaid', 'product_mrp' => 100 ) ) );
	assert_ithink_error( 'validation', fn () => $c->get_rates( array( 'to_pincode' => '400061', 'weight_g' => 10001, 'payment' => 'prepaid', 'product_mrp' => 100 ) ) );
	$no_origin = new Priniti_IThink_Client( test_config( array( 'origin_pincode' => '' ) ), new Fake_Transport() );
	assert_ithink_error( 'config', fn () => $no_origin->get_rates( array( 'to_pincode' => '400061', 'weight_g' => 500, 'payment' => 'prepaid', 'product_mrp' => 100 ) ) );
} );

test( 'client: authentication failures are recognised (HTTP and message)', function () {
	$log = new Fake_Logger();
	$c   = new Priniti_IThink_Client( test_config(), new Fake_Transport( array( array( 'status' => 401, 'body' => '' ) ) ), $log );
	assert_ithink_error( 'auth', fn () => $c->check_pincode( '122002' ) );
	$c = new Priniti_IThink_Client( test_config(), new Fake_Transport( array( array( 'status' => 200, 'body' => json_encode( array( 'status' => 'error', 'status_code' => 400, 'html_message' => 'Invalid access token or secret key ' . TEST_SECRET ) ) ) ) ), $log );
	$e = assert_ithink_error( 'auth', fn () => $c->check_pincode( '122002' ) );
	assert_not_contains( TEST_SECRET, $e->getMessage(), 'secret echoed by the API is scrubbed' );
	assert_not_contains( TEST_SECRET, $log->all() );
} );

test( 'client: API error messages are redacted', function () {
	$body = json_encode( array( 'status' => 'error', 'status_code' => 400, 'html_message' => '<b>Pincode not serviceable</b> for test.customer@example.com 9876543210' ) );
	$c    = new Priniti_IThink_Client( test_config(), new Fake_Transport( array( array( 'status' => 200, 'body' => $body ) ) ) );
	$e    = assert_ithink_error( 'api', fn () => $c->check_pincode( '122002' ) );
	assert_same( 'iThink returned an error: Pincode not serviceable for [email] [phone]', $e->getMessage() );
} );

test( 'client: read calls retry once on timeout; invalid JSON is reported', function () {
	$t = new Fake_Transport( array( new Priniti_IThink_Error( 'timeout', 'timed out' ), ok_json( array( 'data' => array( '122002' => array() ) ) ) ) );
	$c = new Priniti_IThink_Client( test_config(), $t );
	assert_same( array(), $c->check_pincode( '122002' ) );
	assert_same( 2, count( $t->calls ) );

	$t = new Fake_Transport( array( array( 'status' => 200, 'body' => '<html>' ), array( 'status' => 200, 'body' => '<html>' ) ) );
	assert_ithink_error( 'invalid_response', fn () => ( new Priniti_IThink_Client( test_config(), $t ) )->check_pincode( '122002' ) );
	assert_same( 2, count( $t->calls ) );
} );

test( 'client: create and cancel are never retried automatically', function () {
	$t = new Fake_Transport( array( new Priniti_IThink_Error( 'timeout', 'timed out' ) ) );
	$c = new Priniti_IThink_Client( test_config(), $t );
	assert_ithink_error( 'timeout', fn () => $c->create_shipment( array( 'order' => 'X' ) ) );
	assert_same( 1, count( $t->calls ) );

	$t = new Fake_Transport( array( array( 'status' => 502, 'body' => '' ) ) );
	$c = new Priniti_IThink_Client( test_config(), $t );
	assert_ithink_error( 'server', fn () => $c->cancel( array( '1234567890123' ) ) );
	assert_same( 1, count( $t->calls ) );
} );

test( 'client: create sends pickup id and options beside shipments, parses the AWB', function () {
	$t = new Fake_Transport( array( created_response( '9988776655443' ) ) );
	$c = new Priniti_IThink_Client( test_config( array( 'logistics' => 'Delhivery', 's_type' => 'surface' ) ), $t );
	$r = $c->create_shipment( array( 'order' => 'TEST-1001' ) );
	assert_same( '9988776655443', $r['awb'] );
	assert_same( 'delhivery', $r['courier'] );
	assert_same( 'https://track.example/awb', $r['tracking_url'] );
	$data = $t->calls[0]['body']['data'];
	assert_same( '1293', $data['pickup_address_id'] );
	assert_same( 'delhivery', $data['logistics'] );
	assert_same( 'surface', $data['s_type'] );
	assert_same( 'forward', $data['order_type'] );
	assert_same( 1, count( $data['shipments'] ) );
} );

test( 'client: create reports an item-level failure as an API error', function () {
	$res = ok_json( array( 'data' => array( '1' => array( 'status' => 'Fail', 'remark' => 'Pickup address not active', 'waybill' => '' ) ) ) );
	$c   = new Priniti_IThink_Client( test_config(), new Fake_Transport( array( $res ) ) );
	$e   = assert_ithink_error( 'api', fn () => $c->create_shipment( array( 'order' => 'X' ) ) );
	assert_true( str_contains( $e->getMessage(), 'Pickup address not active' ) );
} );

test( 'client: label, cancel and track parse documented answers and limits', function () {
	$t = new Fake_Transport(
		array(
			ok_json( array( 'file_name' => 'http://pre-alpha.ithinklogistics.com/uploads/shipping/x.pdf' ) ),
			ok_json( array( 'data' => array( '1' => array( 'status' => 'Success', 'remark' => '', 'refnum' => 'TEST-1001' ) ) ) ),
			ok_json( array( 'data' => array( '1234567890123' => array( 'current_status' => 'In Transit', 'current_status_code' => 'UD', 'logistic' => 'Delhivery', 'expected_delivery_date' => '2026-11-02', 'last_scan_details' => array( 'scan_location' => 'Gurgaon', 'status_date_time' => '2026-10-30 10:00' ) ) ) ) ),
		)
	);
	$c = new Priniti_IThink_Client( test_config(), $t );
	assert_same( 'https://pre-alpha.ithinklogistics.com/uploads/shipping/x.pdf', $c->label_url( array( '1234567890123' ) ) );
	assert_same( true, $c->cancel( array( '1234567890123' ) )[0]['ok'] );
	assert_same( '1234567890123', $t->calls[1]['body']['data']['awb_numbers'] );
	$track = $c->track( array( '1234567890123' ) );
	assert_same( 'In Transit', $track['1234567890123']['status'] );
	assert_same( '1234567890123', $t->calls[2]['body']['data']['awb_number_list'] );
	assert_ithink_error( 'validation', fn () => $c->track( range( 1000000001, 1000000011 ) ) );
} );

test( 'client: debug logging is redacted (no credentials or personal data)', function () {
	$log = new Fake_Logger();
	$c   = new Priniti_IThink_Client( test_config( array( 'debug' => true ) ), new Fake_Transport( array( created_response() ) ), $log );
	$c->create_shipment( Priniti_IThink_Payload::shipment( test_order(), test_package(), test_config() ) );
	$all = $log->all();
	assert_true( str_contains( $all, 'request create' ), 'request is logged' );
	foreach ( array( TEST_TOKEN, TEST_SECRET, 'Test Customer', '12 Test Street', '9876543210', 'test.customer@example.com' ) as $needle ) {
		assert_not_contains( $needle, $all );
	}
} );
