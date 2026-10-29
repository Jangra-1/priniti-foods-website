<?php
/**
 * Tests for the iThink Logistics module (plugin/priniti-core/includes/ithink). Plain PHP, no WordPress or
 * WooCommerce needed: the API is faked by a scripted transport and orders by an in-memory store, so these tests
 * never touch the network. Run with `npm run test:php` (php tests/php/run.php).
 */

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

foreach ( glob( __DIR__ . '/*-test.php' ) as $file ) {
	require $file;
}

exit( priniti_test_run() );
