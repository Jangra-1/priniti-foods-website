<?php
/**
 * Product category flags (reference/nextjs/types/category.ts: origin, published, coverProductSlug).
 * The WooCommerce categories endpoint has no meta support, so these are exposed on wp/v2/product_cat.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function (): void {
		$auth = static fn (): bool => current_user_can( 'manage_product_terms' );

		register_term_meta(
			'product_cat',
			Priniti_Meta::HIDE_WHEN_EMPTY,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true, // Not sensitive; lets the import tool set it via wp/v2/product_cat (writes need manage_product_terms).
				'sanitize_callback' => static fn ( $v ): string => '1' === (string) $v ? '1' : '',
				'auth_callback'     => $auth,
			)
		);
		register_term_meta(
			'product_cat',
			Priniti_Meta::ORIGIN,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true, // Not sensitive; lets the import tool set it via wp/v2/product_cat (writes need manage_product_terms).
				'sanitize_callback' => static fn ( $v ): string => in_array( $v, array( 'official', 'ecommerce' ), true ) ? $v : 'official',
				'auth_callback'     => $auth,
			)
		);
		register_term_meta(
			'product_cat',
			Priniti_Meta::COVER_PRODUCT,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true, // Not sensitive; lets the import tool set it via wp/v2/product_cat (writes need manage_product_terms).
				'sanitize_callback' => 'sanitize_title',
				'auth_callback'     => $auth,
			)
		);
	}
);
