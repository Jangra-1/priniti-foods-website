<?php
declare(strict_types=1);

test( 'status: documented codes map onto the journey', function () {
	assert_same( array( 'stage' => 'packed', 'attention' => false ), Priniti_IThink_Status::map( 'UD', 'Manifested' ) );
	assert_same( array( 'stage' => 'shipped', 'attention' => false ), Priniti_IThink_Status::map( 'UD', 'In Transit' ) );
	assert_same( array( 'stage' => 'shipped', 'attention' => true ), Priniti_IThink_Status::map( 'UD', 'Undelivered' ) );
	assert_same( array( 'stage' => 'delivered', 'attention' => false ), Priniti_IThink_Status::map( 'DL', 'Delivered' ) );
	assert_same( array( 'stage' => null, 'attention' => true ), Priniti_IThink_Status::map( 'DL', 'RTO Delivered' ) );
	assert_same( array( 'stage' => null, 'attention' => true ), Priniti_IThink_Status::map( 'CN', 'Cancelled' ) );
	assert_same( array( 'stage' => null, 'attention' => true ), Priniti_IThink_Status::map( 'RT', 'RTO In Transit' ) );
	assert_same( array( 'stage' => null, 'attention' => true ), Priniti_IThink_Status::map( 'UD', 'REV Picked Up' ) );
	assert_same( array( 'stage' => null, 'attention' => true ), Priniti_IThink_Status::map( 'Lost', 'Lost' ) );
} );

test( 'status: only forward moves within processing..delivered', function () {
	assert_true( Priniti_IThink_Status::advances( 'processing', 'packed' ) );
	assert_true( Priniti_IThink_Status::advances( 'packed', 'delivered' ) );
	assert_same( false, Priniti_IThink_Status::advances( 'delivered', 'shipped' ) );
	assert_same( false, Priniti_IThink_Status::advances( 'cancelled', 'shipped' ) );
	assert_same( false, Priniti_IThink_Status::advances( 'pending', 'packed' ) );
	assert_same( false, Priniti_IThink_Status::advances( 'shipped', null ) );
} );
