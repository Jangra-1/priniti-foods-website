<?php
/**
 * LOCAL PREVIEW ONLY: never deploy this file.
 *
 * WooCommerce cannot be downloaded into the sandboxed dev environment, so this must-use plugin fakes just
 * enough of it to exercise the theme shell locally: the product_cat taxonomy with the 10 reference
 * categories, and an in-memory Store API cart (wc/store/v1/cart, update-item, remove-item, add-item).
 * Images are served from reference/nextjs/public via a symlink at /ref.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WooCommerce' ) ) {
	return;
}

add_action(
	'init',
	static function (): void {
		register_taxonomy( 'product_cat', 'post', array( 'public' => true, 'rewrite' => array( 'slug' => 'category' ) ) );
		if ( get_option( 'priniti_dev_seeded' ) ) {
			return;
		}
		$cats = array(
			'indian-traditional-namkeen' => 'Indian Traditional Namkeen',
			'potato-chips'               => 'Potato Chips',
			'charchare-sticks'           => 'CharChare Sticks',
			'popcorn'                    => 'Popcorn',
			'puffs-fryums'               => 'Puffs & Fryums',
			'ringo-star-rings'           => 'Ringo Star Rings',
			'rusk'                       => 'Rusk',
			'sweets'                     => 'Sweets',
			'cookies'                    => 'Cookies',
			'donut-cakes'                => 'Donut Cakes',
			'combos'                     => 'Combos',
		);
		$i = 0;
		foreach ( $cats as $slug => $name ) {
			$term = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
			if ( ! is_wp_error( $term ) ) {
				update_term_meta( $term['term_id'], 'order', $i++ );
				if ( 'combos' === $slug ) {
					update_term_meta( $term['term_id'], 'priniti_hide_when_empty', '1' );
				}
			}
		}
		update_option( 'priniti_dev_seeded', 1 );
		flush_rewrite_rules();
	}
);

/**
 * Pretend WooCommerce is active for the islands config (Store API enabled).
 */
add_filter( 'priniti_store_api_enabled', '__return_true' );

function priniti_dev_cart_items(): array {
	$items = get_option( 'priniti_dev_cart' );
	if ( is_array( $items ) ) {
		return $items;
	}
	$items = array(
		array( 'key' => 'k1', 'id' => 101, 'slug' => 'gulab-jamun', 'name' => 'Gulab Jamun', 'pack' => '1 Kg', 'variation' => array(), 'price' => 48000, 'regular' => 52000, 'qty' => 1 ),
		array( 'key' => 'k2', 'id' => 102, 'slug' => 'chips-cream-n-onion', 'name' => 'Potato Chips Cream &#8216;n&#8217; Onion', 'pack' => '', 'variation' => array(), 'price' => 2000, 'regular' => 2000, 'qty' => 3 ),
		array( 'key' => 'k3', 'id' => 103, 'slug' => 'ajwain-cookies', 'name' => 'Ajwain Cookies', 'pack' => '', 'variation' => array( array( 'attribute' => 'Pack size', 'value' => '300 g' ) ), 'price' => 8500, 'regular' => 8500, 'qty' => 2 ),
	);
	update_option( 'priniti_dev_cart', $items );
	return $items;
}

function priniti_dev_cart_response(): WP_REST_Response {
	$items = priniti_dev_cart_items();
	$out   = array();
	$total = 0;
	$count = 0;
	foreach ( $items as $it ) {
		$img   = home_url( '/ref/images/products/' . $it['slug'] . '-1.webp' );
		$out[] = array(
			'key'             => $it['key'],
			'id'              => $it['id'],
			'quantity'        => $it['qty'],
			'name'            => $it['name'],
			'permalink'       => home_url( '/product/' . $it['slug'] . '/' ),
			'images'          => array( array( 'src' => $img, 'thumbnail' => $img, 'alt' => '' ) ),
			'variation'       => $it['variation'],
			'item_data'       => $it['pack'] ? array( array( 'name' => 'Pack size', 'value' => $it['pack'] ) ) : array(),
			'quantity_limits' => array( 'minimum' => 1, 'maximum' => 10, 'multiple_of' => 1, 'editable' => true ),
			'prices'          => array( 'price' => (string) $it['price'], 'regular_price' => (string) $it['regular'], 'sale_price' => (string) $it['price'], 'currency_minor_unit' => 2 ),
		);
		$total += $it['price'] * $it['qty'];
		$count += $it['qty'];
	}
	$res = new WP_REST_Response(
		array(
			'items'       => $out,
			'items_count' => $count,
			'totals'      => array( 'total_items' => (string) $total, 'total_items_tax' => '0', 'currency_minor_unit' => 2 ),
		)
	);
	$res->header( 'Nonce', 'dev-nonce' );
	return $res;
}

add_action(
	'rest_api_init',
	static function (): void {
		$open = '__return_true';
		register_rest_route( 'wc/store/v1', '/cart', array( 'methods' => 'GET', 'permission_callback' => $open, 'callback' => 'priniti_dev_cart_response' ) );
		register_rest_route(
			'wc/store/v1',
			'/cart/update-item',
			array(
				'methods'             => 'POST',
				'permission_callback' => $open,
				'callback'            => static function ( WP_REST_Request $r ) {
					$items = priniti_dev_cart_items();
					foreach ( $items as &$it ) {
						if ( $it['key'] === $r['key'] ) {
							$it['qty'] = max( 1, min( 10, (int) $r['quantity'] ) );
						}
					}
					update_option( 'priniti_dev_cart', $items );
					return priniti_dev_cart_response();
				},
			)
		);
		register_rest_route(
			'wc/store/v1',
			'/cart/remove-item',
			array(
				'methods'             => 'POST',
				'permission_callback' => $open,
				'callback'            => static function ( WP_REST_Request $r ) {
					update_option( 'priniti_dev_cart', array_values( array_filter( priniti_dev_cart_items(), static fn ( $it ) => $it['key'] !== $r['key'] ) ) );
					return priniti_dev_cart_response();
				},
			)
		);
		register_rest_route(
			'wc/store/v1',
			'/cart/reset',
			array(
				'methods'             => 'POST',
				'permission_callback' => $open,
				'callback'            => static function () {
					delete_option( 'priniti_dev_cart' );
					return priniti_dev_cart_response();
				},
			)
		);
	}
);
