<?php
declare(strict_types=1);

test( 'payload: builds the documented shipment with strings, state name and prepaid mode', function () {
	$s = Priniti_IThink_Payload::shipment( test_order(), test_package(), test_config() );
	assert_same( 'TEST-1001', $s['order'] );
	assert_same( '29-10-2026', $s['order_date'] );
	assert_same( '528.00', $s['total_amount'] );
	assert_same( '9876543210', $s['phone'] );
	assert_same( 'Haryana', $s['state'] );
	assert_same( '1.45', $s['weight'], 'grams to kg without rounding' );
	assert_same( '30', $s['shipment_length'] );
	assert_same( 'Prepaid', $s['payment_mode'] );
	assert_same( '0', $s['cod_amount'] );
	assert_same( '1293', $s['return_address_id'] );
	assert_same( '34463', $s['store_id'] );
	assert_same( array( 'product_name' => 'Aloo Bhujia 400 g', 'product_sku' => 'ALB-400', 'product_quantity' => '2', 'product_price' => '105.60' ), $s['products'][0] );
} );

test( 'payload: weight follows the configured unit exactly', function () {
	assert_same( '1450', Priniti_IThink_Payload::shipment( test_order(), test_package(), test_config( array( 'weight_unit' => 'g' ) ) )['weight'] );
	assert_same( '0.005', Priniti_IThink_Payload::weight( 5, 'kg' ) );
	assert_same( '2', Priniti_IThink_Payload::weight( 2000, 'kg' ) );
} );

test( 'payload: COD sends the order total as cod_amount', function () {
	$s = Priniti_IThink_Payload::shipment( test_order( array( 'payment' => 'cod' ) ), test_package(), test_config() );
	assert_same( 'COD', $s['payment_mode'] );
	assert_same( '528.00', $s['cod_amount'] );
} );

test( 'payload: refuses missing weight unit, package data and bad contact details', function () {
	$e = assert_ithink_error( 'validation', fn () => Priniti_IThink_Payload::shipment( test_order(), test_package(), test_config( array( 'weight_unit' => '' ) ) ) );
	assert_true( str_contains( $e->getMessage(), 'PRINITI_ITHINK_WEIGHT_UNIT' ) );
	$e = assert_ithink_error( 'validation', fn () => Priniti_IThink_Payload::shipment( test_order(), test_package( array( 'weight_g' => 0, 'height_cm' => 0.0 ) ), test_config() ) );
	assert_true( str_contains( $e->getMessage(), 'weight' ) && str_contains( $e->getMessage(), 'height' ) );
	assert_ithink_error( 'validation', fn () => Priniti_IThink_Payload::shipment( test_order( array( 'phone' => '12345' ) ), test_package(), test_config() ) );
	assert_ithink_error( 'validation', fn () => Priniti_IThink_Payload::shipment( test_order( array( 'pincode' => '022002' ) ), test_package(), test_config() ) );
	assert_ithink_error( 'validation', fn () => Priniti_IThink_Payload::shipment( test_order(), test_package(), test_config( array( 'return_address_id' => null ) ) ) );
} );

test( 'payload: at most 40 products per shipment', function () {
	$items = array_fill( 0, 41, array( 'name' => 'Item', 'sku' => '', 'qty' => 1, 'unit_price' => 10.0 ) );
	assert_ithink_error( 'validation', fn () => Priniti_IThink_Payload::shipment( test_order( array( 'items' => $items ) ), test_package(), test_config() ) );
} );

test( 'payload: mobile numbers are normalised', function () {
	assert_same( '9876543210', Priniti_IThink_Payload::mobile( '09876543210' ) );
	assert_same( '9876543210', Priniti_IThink_Payload::mobile( '+91-98765-43210' ) );
	assert_same( '', Priniti_IThink_Payload::mobile( '5876543210' ) );
} );
