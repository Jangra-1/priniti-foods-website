<?php
/**
 * GENERATED from reference/nextjs/data/integrations.ts by scripts/export-reference-data.ts. Do not edit by hand:
 * change the reference, then run `npm run export:data`.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

return array(
	array(
		'id' => 'pricing',
		'label' => 'Product pricing',
		'detail' => 'Real MRP and selling prices have not been supplied for any product.',
	),
	array(
		'id' => 'shipping',
		'label' => 'Shipping rules and rates',
		'detail' => 'Delivery areas, charges and any free-shipping rule are not configured.',
	),
	array(
		'id' => 'tax',
		'label' => 'Tax / GST configuration',
		'detail' => 'GST rates and invoice rules are not configured.',
	),
	array(
		'id' => 'payment',
		'label' => 'Payment gateway',
		'detail' => 'No gateway is connected, and no payment is taken or simulated.',
	),
	array(
		'id' => 'orders',
		'label' => 'Order backend and database',
		'detail' => 'Orders cannot be created, stored or tracked yet.',
	),
	array(
		'id' => 'notifications',
		'label' => 'Order confirmation (email / WhatsApp)',
		'detail' => 'No confirmation message can be sent yet.',
	),
);
