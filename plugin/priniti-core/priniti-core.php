<?php
/**
 * Plugin Name: Priniti Core
 * Plugin URI: https://shop.prinitifoods.com
 * Description: Store data and business logic for the Priniti Foods storefront: product fields, pack sizes, category flags, checkout rules (India, full name, mobile, pincode), sign-in with email or mobile, sign-up, contact enquiries, newsletter, order tracking and journey statuses. Presentation lives in the "priniti" theme.
 * Version: 0.2.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 * Author: Priniti Foods Pvt. Ltd.
 * Text Domain: priniti-core
 * License: Proprietary
 * WC requires at least: 9.0
 * WC tested up to: 11.1
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

define( 'PRINITI_CORE_VERSION', '0.2.0' );
define( 'PRINITI_CORE_FILE', __FILE__ );
define( 'PRINITI_CORE_DIR', plugin_dir_path( __FILE__ ) );

require_once PRINITI_CORE_DIR . 'includes/meta-keys.php';
require_once PRINITI_CORE_DIR . 'includes/product-fields.php';
require_once PRINITI_CORE_DIR . 'includes/category-fields.php';
require_once PRINITI_CORE_DIR . 'includes/cart.php';
require_once PRINITI_CORE_DIR . 'includes/forms.php';
require_once PRINITI_CORE_DIR . 'includes/accounts.php';
require_once PRINITI_CORE_DIR . 'includes/contact.php';
require_once PRINITI_CORE_DIR . 'includes/newsletter.php';
require_once PRINITI_CORE_DIR . 'includes/tracking.php';
require_once PRINITI_CORE_DIR . 'includes/checkout.php';
require_once PRINITI_CORE_DIR . 'includes/admin.php';

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage (enabled on the live store).
 */
add_action(
	'before_woocommerce_init',
	static function (): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', PRINITI_CORE_FILE, true );
		}
	}
);
