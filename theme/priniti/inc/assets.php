<?php
/**
 * Styles, fonts and the interactive islands bundle.
 *
 * assets/dist is produced by `npm run build` (CI builds it for deployment; it is not committed).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

/**
 * Entry file of the islands bundle from the Vite manifest, or null when the theme has not been built.
 */
function priniti_islands_entry(): ?string {
	$manifest = PRINITI_DIR . '/assets/dist/js/.vite/manifest.json';
	if ( ! is_readable( $manifest ) ) {
		return null;
	}
	$data  = json_decode( (string) file_get_contents( $manifest ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$entry = is_array( $data ) ? ( $data['theme/priniti/assets/src/js/app.tsx']['file'] ?? null ) : null;
	return is_string( $entry ) ? $entry : null;
}

/**
 * Data the islands need (window.PRINITI). Public, non-sensitive values only.
 *
 * @return array<string, mixed>
 */
function priniti_islands_config(): array {
	$config = priniti_site_config();
	$nav    = priniti_navigation();
	$links  = static fn ( array $items ): array => array_map(
		static fn ( array $l ): array => array(
			'label' => $l['label'],
			'href'  => priniti_url( $l['path'] ),
		),
		$items
	);

	$wc = (bool) apply_filters( 'priniti_store_api_enabled', class_exists( 'WooCommerce' ) );

	return array(
		'siteName'   => $config['name'],
		'urls'       => array(
			'home'     => priniti_url( '/' ),
			'shop'     => priniti_url( '/shop' ),
			'cart'     => priniti_url( '/cart' ),
			'checkout' => priniti_url( '/checkout' ),
			'login'    => priniti_url( '/login' ),
			'signup'   => priniti_url( '/signup' ),
			'search'   => priniti_url( '/search' ),
		),
		'nav'        => array(
			'mobileShop' => $links( $nav['mobile']['shop'] ),
			'mobileInfo' => $links( $nav['mobile']['info'] ),
		),
		'categories' => array_map(
			static fn ( array $c ): array => array(
				'slug' => $c['slug'],
				'name' => $c['name'],
				'href' => $c['href'],
			),
			priniti_get_categories()
		),
		'searchIndexUrl' => rest_url( 'priniti/v1/search-index' ),
		'storeApi'   => array(
			'enabled' => $wc,
			'root'    => rest_url( 'wc/store/v1/' ),
			// Store API nonce for cart writes. Public by design (it is tied to the visitor's session).
			'nonce'   => $wc ? wp_create_nonce( 'wc_store_api' ) : '',
		),
		'commerce'   => array(
			'currency'              => $config['commerce']['currency'],
			'freeShippingThreshold' => $config['commerce']['free_shipping_threshold'],
			'maxQuantityPerLine'    => (int) $config['commerce']['max_quantity_per_line'],
			'pricesIncludeTax'      => $wc && 'incl' === get_option( 'woocommerce_tax_display_cart' ),
		),
	);
}

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$css = PRINITI_DIR . '/assets/dist/app.css';
		if ( is_readable( $css ) ) {
			wp_enqueue_style( 'priniti', PRINITI_URI . '/assets/dist/app.css', array(), (string) filemtime( $css ) );
		}

		$entry = priniti_islands_entry();
		if ( $entry ) {
			wp_enqueue_script( 'priniti-islands', PRINITI_URI . '/assets/dist/js/' . $entry, array(), null, array( 'in_footer' => true ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- hashed filename.
			wp_add_inline_script( 'priniti-islands', 'window.PRINITI=' . wp_json_encode( priniti_islands_config() ) . ';', 'before' );
		}

		// Classic-theme block library CSS is not needed for the storefront's own templates.
		wp_dequeue_style( 'classic-theme-styles' );
	}
);

/**
 * Load the islands bundle as an ES module.
 */
add_filter(
	'script_loader_tag',
	static function ( string $tag, string $handle ): string {
		if ( 'priniti-islands' === $handle ) {
			$tag = str_replace( '<script src=', '<script type="module" src=', $tag );
			$tag = str_replace( "<script src='", "<script type='module' src='", $tag );
		}
		return $tag;
	},
	10,
	2
);

/**
 * Preload the two fonts used above the fold (body + display), as next/font does.
 */
add_action(
	'wp_head',
	static function (): void {
		foreach ( array( 'inter-latin-400-normal', 'poppins-latin-700-normal' ) as $font ) {
			if ( is_readable( PRINITI_DIR . "/assets/dist/fonts/{$font}.woff2" ) ) {
				printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( PRINITI_URI . "/assets/dist/fonts/{$font}.woff2" ) );
			}
		}
	},
	2
);
