<?php
/**
 * LOCAL PREVIEW ONLY: never deploy this file.
 *
 * WooCommerce cannot be downloaded into the sandboxed dev environment, so this must-use plugin stands in for it
 * just enough to render every theme template locally:
 *  - the catalog and categories come from tools/dev/catalog.json (reference data, see export-catalog.ts) through
 *    the theme's own `priniti_pre_catalog` / `priniti_pre_categories` filters;
 *  - `product` posts, `product_cat` terms and the Shop/Cart/Checkout/My account pages exist so URLs resolve;
 *  - the WooCommerce conditional tags the theme uses are shimmed;
 *  - an in-memory Store API cart (cart, add-item, update-item, remove-item, apply-coupon) backs the islands.
 * Images are served from reference/nextjs/public via a symlink at /ref.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WooCommerce' ) ) {
	return;
}

function priniti_dev_data(): array {
	static $data = null;
	if ( null === $data ) {
		$file = __DIR__ . '/catalog.json';
		$real = is_link( __FILE__ ) ? dirname( (string) readlink( __FILE__ ) ) . '/catalog.json' : $file;
		$data = json_decode( (string) @file_get_contents( is_readable( $real ) ? $real : $file ), true ) ?: array( 'catalog' => array(), 'categories' => array() ); // phpcs:ignore
	}
	return $data;
}

add_filter( 'priniti_pre_catalog', static fn () => priniti_dev_data()['catalog'] );
add_filter( 'priniti_pre_categories', static fn () => priniti_dev_data()['categories'] );
add_filter( 'priniti_store_api_enabled', '__return_true' );

/* ---- Content model stand-ins ---- */

add_action(
	'init',
	static function (): void {
		register_post_type( 'product', array( 'public' => true, 'rewrite' => array( 'slug' => 'product' ), 'label' => 'Products' ) );
		register_taxonomy( 'product_cat', 'product', array( 'public' => true, 'rewrite' => array( 'slug' => 'category' ) ) );

		// Re-seed whenever catalog.json lists different products (e.g. after `export-catalog.ts --ecomm`).
		$seed = md5( implode( ',', array_column( priniti_dev_data()['catalog'], 'slug' ) ) );
		if ( get_option( 'priniti_dev_seed' ) === $seed ) {
			return;
		}
		foreach ( priniti_dev_data()['categories'] as $c ) {
			if ( ! term_exists( $c['slug'], 'product_cat' ) ) {
				wp_insert_term( $c['name'], 'product_cat', array( 'slug' => $c['slug'] ) );
			}
		}
		foreach ( priniti_dev_data()['catalog'] as $p ) {
			if ( ! get_page_by_path( $p['slug'], OBJECT, 'product' ) ) {
				wp_insert_post( array( 'post_type' => 'product', 'post_status' => 'publish', 'post_name' => $p['slug'], 'post_title' => $p['name'] ) );
			}
		}
		foreach ( array( 'shop' => 'Shop', 'cart' => 'Cart', 'checkout' => 'Checkout', 'my-account' => 'My account' ) as $slug => $title ) {
			if ( ! get_page_by_path( $slug ) ) {
				wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title ) );
			}
		}
		update_option( 'priniti_dev_seed', $seed );
		delete_option( 'priniti_rewrite_version' );
	}
);

/* ---- WooCommerce conditional-tag shims ---- */

if ( ! function_exists( 'is_woocommerce' ) ) {
	function is_woocommerce() { return is_shop() || is_product() || is_product_category(); }
	function is_product() { return is_singular( 'product' ); }
	function is_product_category( $t = '' ) { return is_tax( 'product_cat', $t ); }
	function is_product_tag() { return false; }
	function is_product_taxonomy() { return is_tax( 'product_cat' ); }
	function is_shop() { return is_page( 'shop' ); }
	function is_cart() { return is_page( 'cart' ); }
	function is_checkout() { return is_page( 'checkout' ); }
	function is_account_page() { return is_page( 'my-account' ); }
	function is_order_received_page() { return false; }
	function is_wc_endpoint_url( $e = '' ) { return false; }
	function wc_get_page_permalink( $page ) { return home_url( '/' . ( 'myaccount' === $page ? 'my-account' : $page ) . '/' ); }
	function wc_lostpassword_url() { return home_url( '/my-account/lost-password/' ); }
}

/* ---- In-memory Store API cart ---- */

function priniti_dev_cart_items(): array {
	$items = get_option( 'priniti_dev_cart2' );
	return is_array( $items ) ? $items : array();
}

function priniti_dev_find_variant( int $id ): ?array {
	foreach ( priniti_dev_data()['catalog'] as $p ) {
		foreach ( $p['variants'] as $v ) {
			if ( (int) $v['id'] === $id ) {
				return array( $p, $v );
			}
		}
	}
	return null;
}

/** Same per-line limit as priniti-core: 3 for single packs (the 1/2/3 selector), otherwise 10. */
function priniti_dev_max_qty( array $v ): int {
	return 1 === ( $v['pcs'] ?? null ) ? 3 : 10;
}

function priniti_dev_cart_response(): WP_REST_Response {
	$out   = array();
	$total = 0;
	$count = 0;
	foreach ( priniti_dev_cart_items() as $key => $it ) {
		$found = priniti_dev_find_variant( (int) $it['id'] );
		if ( ! $found ) {
			continue;
		}
		[ $p, $v ] = $found;
		$unit      = (float) ( $v['price'] ?? 0 );
		// Single packs: the real cart prices 2-3 packs with priniti-core's multi-pack rule; use the same function.
		if ( 1 === ( $v['pcs'] ?? null ) && function_exists( 'priniti_core_multipack_unit_price' ) ) {
			$unit = priniti_core_multipack_unit_price( (float) $v['unitMrp'], (int) $it['qty'] );
		}
		$price     = (int) round( $unit * 100 );
		$regular   = (int) round( ( $v['mrp'] ?? $v['price'] ?? 0 ) * 100 );
		$out[]     = array(
			'key'             => $key,
			'id'              => (int) $v['id'],
			'quantity'        => (int) $it['qty'],
			'name'            => $p['name'],
			'permalink'       => $p['href'],
			'images'          => $p['images'] ? array( array( 'src' => $p['images'][0]['src'], 'thumbnail' => $p['images'][0]['src'], 'alt' => $p['images'][0]['alt'] ) ) : array(),
			'variation'       => $v['attributes'],
			'item_data'       => ( ! $v['attributes'] && $v['label'] ) ? array( array( 'name' => 'Pack size', 'value' => $v['label'] ) ) : array(),
			'quantity_limits' => array( 'minimum' => 1, 'maximum' => priniti_dev_max_qty( $v ), 'multiple_of' => 1, 'editable' => true ),
			'prices'          => array( 'price' => (string) $price, 'regular_price' => (string) $regular, 'sale_price' => (string) $price, 'currency_minor_unit' => 2 ),
		);
		$total += $price * (int) $it['qty'];
		$count += (int) $it['qty'];
	}
	$res = new WP_REST_Response( array( 'items' => $out, 'items_count' => $count, 'coupons' => array(), 'totals' => array( 'total_items' => (string) $total, 'total_items_tax' => '0', 'currency_minor_unit' => 2 ) ) );
	$res->header( 'Nonce', 'dev-nonce' );
	return $res;
}

add_action(
	'rest_api_init',
	static function (): void {
		$open  = '__return_true';
		$route = static fn ( string $path, string $method, callable $cb ) => register_rest_route( 'wc/store/v1', $path, array( 'methods' => $method, 'permission_callback' => $open, 'callback' => $cb ) );
		$route( '/cart', 'GET', 'priniti_dev_cart_response' );
		$route(
			'/cart/add-item',
			'POST',
			static function ( WP_REST_Request $r ) {
				$found = priniti_dev_find_variant( (int) $r['id'] );
				if ( ! $found || ! $found[1]['purchasable'] ) {
					return new WP_Error( 'woocommerce_rest_product_not_purchasable', 'This product cannot be purchased.', array( 'status' => 400 ) );
				}
				$items = priniti_dev_cart_items();
				$key   = 'k' . (int) $r['id'];
				$qty   = ( $items[ $key ]['qty'] ?? 0 ) + max( 1, (int) $r['quantity'] );
				$max   = priniti_dev_max_qty( $found[1] );
				if ( $qty > $max ) {
					return new WP_Error( 'woocommerce_rest_cart_invalid_quantity', ( 3 === $max ? 'You can add up to 3 packs of this size (2 or 3 packs are 12% off).' : sprintf( 'You can add up to %d of each item.', $max ) ), array( 'status' => 400 ) );
				}
				$items[ $key ] = array( 'id' => (int) $r['id'], 'qty' => $qty );
				update_option( 'priniti_dev_cart2', $items );
				return priniti_dev_cart_response();
			}
		);
		$route(
			'/cart/update-item',
			'POST',
			static function ( WP_REST_Request $r ) {
				$items = priniti_dev_cart_items();
				if ( isset( $items[ $r['key'] ] ) ) {
					$found                     = priniti_dev_find_variant( (int) $items[ $r['key'] ]['id'] );
					$items[ $r['key'] ]['qty'] = max( 1, min( $found ? priniti_dev_max_qty( $found[1] ) : 10, (int) $r['quantity'] ) );
				}
				update_option( 'priniti_dev_cart2', $items );
				return priniti_dev_cart_response();
			}
		);
		$route(
			'/cart/remove-item',
			'POST',
			static function ( WP_REST_Request $r ) {
				$items = priniti_dev_cart_items();
				unset( $items[ $r['key'] ] );
				update_option( 'priniti_dev_cart2', $items );
				return priniti_dev_cart_response();
			}
		);
		$route( '/cart/apply-coupon', 'POST', static fn () => new WP_Error( 'woocommerce_rest_cart_coupon_error', 'Coupon "x" does not exist!', array( 'status' => 400 ) ) );
		$route(
			'/cart/reset',
			'POST',
			static function () {
				delete_option( 'priniti_dev_cart2' );
				return priniti_dev_cart_response();
			}
		);
	}
);
