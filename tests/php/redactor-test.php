<?php
declare(strict_types=1);

test( 'redactor: credentials and personal fields are removed, AWBs kept', function () {
	$r   = new Priniti_IThink_Redactor( array( TEST_SECRET ) );
	$out = $r->data(
		array(
			'access_token' => TEST_TOKEN,
			'note'         => 'secret is ' . TEST_SECRET,
			'shipments'    => array( array( 'name' => 'Test Customer', 'phone' => '9876543210', 'pin' => '122002', 'waybill' => '9876543210' ) ),
		)
	);
	assert_same( '[redacted]', $out['access_token'] );
	assert_same( 'secret is [redacted]', $out['note'] );
	assert_same( '[personal]', $out['shipments'][0]['name'] );
	assert_same( '[personal]', $out['shipments'][0]['phone'] );
	assert_same( '122xxx', $out['shipments'][0]['pin'] );
	assert_same( '9876543210', $out['shipments'][0]['waybill'] );
} );
