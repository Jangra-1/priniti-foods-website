<?php
/**
 * WooCommerce integration for the shell. Catalog, product, cart and checkout templates arrive in later phases.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		add_theme_support( 'woocommerce' );
	}
);

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Wrap WooCommerce's default templates in the design's Container until each page gets its own template.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action(
	'woocommerce_before_main_content',
	static function (): void {
		echo '<div class="' . esc_attr( priniti_container_classes( 'py-6 lg:py-10' ) ) . '">';
	},
	10
);
add_action(
	'woocommerce_after_main_content',
	static function (): void {
		echo '</div>';
	},
	10
);
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * Server-side twin of the UI quantity limit (siteConfig.commerce.maxQuantityPerLine).
 */
add_filter(
	'woocommerce_quantity_input_max',
	static function ( $max ) {
		$limit = (int) priniti_site_config()['commerce']['max_quantity_per_line'];
		return ( $max < 0 || '' === $max || $max > $limit ) ? $limit : $max;
	}
);
add_filter(
	'woocommerce_store_api_product_quantity_maximum',
	static function ( $max ) {
		return min( (int) $max, (int) priniti_site_config()['commerce']['max_quantity_per_line'] );
	}
);
add_filter(
	'woocommerce_add_to_cart_validation',
	static function ( $passed, $product_id, $quantity, $variation_id = 0 ) {
		if ( ! $passed || ! function_exists( 'WC' ) || ! WC()->cart ) {
			return $passed;
		}
		$limit    = (int) priniti_site_config()['commerce']['max_quantity_per_line'];
		$target   = $variation_id ? (int) $variation_id : (int) $product_id;
		$in_cart  = 0;
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( (int) ( $item['variation_id'] ?: $item['product_id'] ) === $target ) {
				$in_cart += (int) $item['quantity'];
			}
		}
		if ( $in_cart + (int) $quantity > $limit ) {
			/* translators: %d: maximum quantity per product */
			wc_add_notice( sprintf( __( 'You can add up to %d of each item.', 'priniti' ), $limit ), 'error' );
			return false;
		}
		return $passed;
	},
	10,
	4
);
