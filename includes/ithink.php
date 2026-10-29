<?php
/**
 * iThink Logistics integration (docs/ITHINK.md). OFF by default: configured only through wp-config.php constants.
 *
 * Always loaded: the classes (no side effects) and the admin screens for verified packed weights and the
 * configuration overview, so shipping data can be prepared before the integration is switched on.
 * Loaded only when PRINITI_ITHINK_ENABLED is true: the order-screen shipment box, the shipping method and the
 * tracking details on /track-order.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

require_once PRINITI_CORE_DIR . 'includes/ithink/class-priniti-ithink-error.php';
require_once PRINITI_CORE_DIR . 'includes/ithink/class-priniti-ithink-config.php';
require_once PRINITI_CORE_DIR . 'includes/ithink/class-priniti-ithink-redactor.php';
require_once PRINITI_CORE_DIR . 'includes/ithink/class-priniti-ithink-client.php';
require_once PRINITI_CORE_DIR . 'includes/ithink/class-priniti-ithink-payload.php';
require_once PRINITI_CORE_DIR . 'includes/ithink/class-priniti-ithink-status.php';
require_once PRINITI_CORE_DIR . 'includes/ithink/class-priniti-ithink-shipments.php';
require_once PRINITI_CORE_DIR . 'includes/ithink/class-priniti-ithink-orders.php';
require_once PRINITI_CORE_DIR . 'includes/ithink/admin.php';

if ( Priniti_IThink::enabled() ) {
	require_once PRINITI_CORE_DIR . 'includes/ithink/shipping-method.php';
	require_once PRINITI_CORE_DIR . 'includes/ithink/track-order.php';
}
