<?php
/**
 * Registers the Priniti product fields.
 *
 * Underscore-prefixed (protected) meta: hidden from the generic Custom Fields box and NOT exposed through the
 * public wp/v2 REST API. The import tool writes them through the authenticated WooCommerce REST API
 * (`meta_data`), and the theme reads them with priniti_core_get_product_field().
 * An admin editing UI for these fields is part of the product-page phase.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function (): void {
		$auth = static fn (): bool => current_user_can( 'edit_products' );

		$string_fields = array( Priniti_Meta::INGREDIENTS, Priniti_Meta::STORAGE, Priniti_Meta::SHIPPING_NOTE, Priniti_Meta::PACK_SIZE, Priniti_Meta::PACK_SOURCE, Priniti_Meta::SOURCE_ID );
		foreach ( $string_fields as $key ) {
			register_post_meta(
				'product',
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => false,
					'sanitize_callback' => 'sanitize_textarea_field',
					'auth_callback'     => $auth,
				)
			);
		}

		$array_fields = array( Priniti_Meta::HIGHLIGHTS, Priniti_Meta::NUTRITION, Priniti_Meta::FAQS, Priniti_Meta::INTERNAL_NOTES );
		foreach ( $array_fields as $key ) {
			register_post_meta(
				'product',
				$key,
				array(
					'type'          => 'array',
					'single'        => true,
					'show_in_rest'  => false,
					'auth_callback' => $auth,
				)
			);
		}

		register_post_meta(
			'product_variation',
			Priniti_Meta::PACK_SOURCE,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => $auth,
			)
		);
	}
);

/**
 * Public accessor for templates. Returns null for unknown keys and for INTERNAL_NOTES (never rendered).
 *
 * @return mixed
 */
function priniti_core_get_product_field( int $product_id, string $key ) {
	$public = array( Priniti_Meta::HIGHLIGHTS, Priniti_Meta::INGREDIENTS, Priniti_Meta::NUTRITION, Priniti_Meta::STORAGE, Priniti_Meta::SHIPPING_NOTE, Priniti_Meta::FAQS, Priniti_Meta::PACK_SIZE );
	if ( ! in_array( $key, $public, true ) ) {
		return null;
	}
	$value = get_post_meta( $product_id, $key, true );
	return ( '' === $value || array() === $value ) ? null : $value;
}
