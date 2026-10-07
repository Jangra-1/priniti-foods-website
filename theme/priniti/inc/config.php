<?php
/**
 * Site configuration and navigation.
 *
 * Mirrors reference/nextjs/data/site.ts and reference/nextjs/data/navigation.ts. Values marked PENDING stay
 * empty until the business confirms them; templates hide anything that is not set (never invent content).
 * Everything here can be overridden with the `priniti_site_config` / `priniti_navigation` filters
 * (for example from the priniti-core plugin) without editing the theme.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

/**
 * Central site configuration (data/site.ts).
 *
 * @return array<string, mixed>
 */
function priniti_site_config(): array {
	static $config = null;
	if ( null !== $config ) {
		return $config;
	}

	$config = apply_filters(
		'priniti_site_config',
		array(
			'name'         => 'Priniti Foods',
			'legal_name'   => 'Priniti Foods Pvt. Ltd.',
			'tagline'      => 'Snacks for every moment',
			'description'  => 'Namkeen, chips, puffs, popcorn, sweets, cookies, rusk and combo packs from Priniti Foods.',
			// Optional announcement-bar text. Hidden until the business supplies a confirmed message.
			'announcement' => null,
			// Original Priniti logo (converted from the supplied CorelDRAW file).
			'logo'         => array(
				'src'    => PRINITI_URI . '/assets/images/logo/priniti-logo.svg',
				'width'  => 346,
				'height' => 200,
			),
			// PENDING: Indian food sellers typically need to show their FSSAI licence number.
			'fssai_license' => null,
			'commerce'     => array(
				'currency'                => 'INR',
				// UI limit; the server enforces the same limit (inc/woocommerce.php).
				'max_quantity_per_line'   => 10,
			),
			// PENDING: set 'href' to the real profile URL. Entries without an href are not rendered.
			'social'       => array(
				array( 'label' => 'Instagram', 'href' => null, 'icon' => 'instagram' ),
				array( 'label' => 'Facebook', 'href' => null, 'icon' => 'facebook' ),
				array( 'label' => 'YouTube', 'href' => null, 'icon' => 'youtube' ),
				array( 'label' => 'X (Twitter)', 'href' => null, 'icon' => 'twitter' ),
			),
		)
	);

	return $config;
}

/**
 * Navigation (data/navigation.ts). Paths are the design's routes; priniti_url() turns them into site URLs.
 *
 * @return array<string, mixed>
 */
function priniti_navigation(): array {
	$links = array(
		'shop'    => array( 'label' => 'Shop', 'path' => '/shop' ),
		'about'   => array( 'label' => 'About', 'path' => '/about' ),
		'contact' => array( 'label' => 'Contact', 'path' => '/contact' ),
	);
	$track = array( 'label' => 'Track order', 'path' => '/track-order' );

	return apply_filters(
		'priniti_navigation',
		array(
			'desktop' => array(
				$links['shop'],
				array( 'label' => 'Categories', 'path' => '/shop', 'has_category_menu' => true ),
				$links['about'],
				$links['contact'],
			),
			'mobile'  => array(
				'shop' => array( $links['shop'] ),
				'info' => array( $links['about'], $links['contact'], $track ),
			),
			'footer'  => array(
				'shop'    => array(
					array( 'label' => 'All products', 'path' => '/shop' ),
					array( 'label' => 'Search', 'path' => '/search' ),
					array( 'label' => 'Cart', 'path' => '/cart' ),
					array( 'label' => 'My account', 'path' => '/my-account' ),
				),
				'support' => array(
					$links['contact'],
					$track,
					array( 'label' => 'Login', 'path' => '/login' ),
					array( 'label' => 'Create account', 'path' => '/signup' ),
				),
				'company' => array(
					array( 'label' => 'About us', 'path' => '/about' ),
					$links['contact'],
				),
				'legal'   => array(
					array( 'label' => 'Shipping Policy', 'path' => '/shipping-policy' ),
					array( 'label' => 'Return & Refund Policy', 'path' => '/return-refund-policy' ),
					array( 'label' => 'Privacy Policy', 'path' => '/privacy-policy' ),
					array( 'label' => 'Terms & Conditions', 'path' => '/terms-and-conditions' ),
					array( 'label' => 'Cookie Policy', 'path' => '/cookie-policy' ),
				),
			),
		)
	);
}
