<?php
declare(strict_types=1);

test( 'config: off by default and lists what is missing without values', function () {
	$c = new Priniti_IThink_Config();
	assert_same( false, $c->enabled() );
	assert_same( 'staging', $c->env() );
	$problems = implode( ' ', $c->problems() );
	assert_true( str_contains( $problems, 'PRINITI_ITHINK_ENABLED' ) );
	assert_true( str_contains( $problems, 'PRINITI_ITHINK_ACCESS_TOKEN' ) );
} );

test( 'config: staging and production endpoints are separate', function () {
	assert_same( 'https://pre-alpha.ithinklogistics.com/api_v3/order/add.json', test_config()->url( 'create' ) );
	assert_same( 'https://pre-alpha.ithinklogistics.com/api_v3/order/track.json', test_config()->url( 'track' ) );
	$prod = test_config( array( 'env' => 'production', 'allow_production' => true ) );
	assert_same( 'https://my.ithinklogistics.com/api_v3/order/add.json', $prod->url( 'create' ) );
	assert_same( 'https://my.ithinklogistics.com/api_v3/rate/check.json', $prod->url( 'rate' ) );
	assert_same( 'https://api.ithinklogistics.com/api_v3/order/track.json', $prod->url( 'track' ) );
} );

test( 'config: production needs the explicit allow flag', function () {
	$c = test_config( array( 'env' => 'production' ) );
	assert_true( str_contains( implode( ' ', $c->problems() ), 'PRINITI_ITHINK_ALLOW_PRODUCTION' ) );
	assert_same( array(), test_config( array( 'env' => 'production', 'allow_production' => true ) )->problems() );
} );

test( 'config: weight unit is never assumed', function () {
	$c = test_config( array( 'weight_unit' => '' ) );
	assert_same( '', $c->weight_unit() );
	assert_true( str_contains( implode( ' ', $c->shipment_problems() ), 'PRINITI_ITHINK_WEIGHT_UNIT' ) );
	assert_same( '', test_config( array( 'weight_unit' => 'lbs' ) )->weight_unit() );
} );

test( 'config: status overview never contains credential values', function () {
	$text = json_encode( test_config()->status() );
	assert_not_contains( TEST_TOKEN, $text );
	assert_not_contains( TEST_SECRET, $text );
	assert_true( str_contains( $text, '"set"' ) );
} );

test( 'config: default box parses "L x W x H" and rejects junk', function () {
	assert_same( array( 'length_cm' => 30.0, 'width_cm' => 20.0, 'height_cm' => 15.5 ), test_config( array( 'default_box' => '30 x 20 x 15.5' ) )->default_box() );
	assert_same( null, test_config( array( 'default_box' => '30x20' ) )->default_box() );
	assert_same( null, test_config( array( 'default_box' => '0x20x10' ) )->default_box() );
} );
