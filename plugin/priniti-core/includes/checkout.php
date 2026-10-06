<?php
/**
 * Checkout rules (reference CheckoutForm): one "Full name" field, Indian mobile and pincode, India only,
 * ship to the billing address, no order notes. Plus the per-line quantity limit (siteConfig.maxQuantityPerLine).
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

const PRINITI_CORE_MAX_QTY = 10;

/** India only (the store sells and ships within India). */
$priniti_core_india = static fn (): array => array( 'IN' => __( 'India', 'woocommerce' ) );
add_filter( 'woocommerce_countries_allowed_countries', $priniti_core_india );
add_filter( 'woocommerce_countries_shipping_countries', $priniti_core_india );
add_filter( 'default_checkout_billing_country', static fn (): string => 'IN' );

/** One address: deliveries go to the billing address; no order notes (not in the design). */
add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );
add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );

add_filter(
	'woocommerce_checkout_fields',
	static function ( array $fields ): array {
		$billing = $fields['billing'] ?? array();
		unset( $billing['billing_first_name'], $billing['billing_last_name'], $billing['billing_company'] );

		$billing['billing_full_name'] = array(
			'label'        => __( 'Full name', 'priniti-core' ),
			'required'     => true,
			'autocomplete' => 'name',
			'priority'     => 5,
		);
		$billing['billing_email']     = array_merge( $billing['billing_email'] ?? array(), array( 'label' => __( 'Email', 'priniti-core' ), 'priority' => 10, 'class' => array() ) );
		$billing['billing_phone']     = array_merge(
			$billing['billing_phone'] ?? array(),
			array(
				'label'             => __( 'Mobile number', 'priniti-core' ),
				'required'          => true,
				'type'              => 'tel',
				'autocomplete'      => 'tel-national',
				'priority'          => 20,
				'class'             => array(),
				'custom_attributes' => array( 'inputmode' => 'numeric', 'maxlength' => '10', 'data-digits' => '' ),
			)
		);
		$billing['billing_address_1'] = array_merge( $billing['billing_address_1'] ?? array(), array( 'label' => __( 'Address line', 'priniti-core' ), 'placeholder' => '', 'priority' => 30, 'class' => array() ) );
		$billing['billing_address_2'] = array_merge( $billing['billing_address_2'] ?? array(), array( 'label' => __( 'Apartment, landmark', 'priniti-core' ), 'label_class' => array(), 'placeholder' => '', 'required' => false, 'priority' => 40, 'class' => array() ) );
		$billing['billing_city']      = array_merge( $billing['billing_city'] ?? array(), array( 'label' => __( 'City', 'priniti-core' ), 'priority' => 50, 'class' => array() ) );
		$billing['billing_state']     = array_merge( $billing['billing_state'] ?? array(), array( 'label' => __( 'State', 'priniti-core' ), 'placeholder' => __( 'Select state', 'priniti-core' ), 'required' => true, 'priority' => 60, 'class' => array() ) );
		$billing['billing_postcode']  = array_merge(
			$billing['billing_postcode'] ?? array(),
			array(
				'label'             => __( 'Pincode', 'priniti-core' ),
				'priority'          => 70,
				'class'             => array(),
				'custom_attributes' => array( 'inputmode' => 'numeric', 'maxlength' => '6', 'data-digits' => '' ),
			)
		);
		// Country is always India: kept for WooCommerce (states, tax, shipping) but not shown.
		$billing['billing_country'] = array_merge( $billing['billing_country'] ?? array(), array( 'priority' => 80, 'class' => array( 'hidden' ), 'default' => 'IN' ) );

		uasort( $billing, static fn ( $a, $b ) => ( $a['priority'] ?? 99 ) <=> ( $b['priority'] ?? 99 ) );
		$fields['billing'] = $billing;
		unset( $fields['shipping'] );
		return $fields;
	},
	20
);

/** The full name is stored as WooCommerce's first and last name. */
add_filter(
	'woocommerce_checkout_posted_data',
	static function ( array $data ): array {
		$full  = trim( (string) ( $data['billing_full_name'] ?? '' ) );
		$parts = preg_split( '/\s+/', $full, 2 );
		$data['billing_first_name'] = $parts[0] ?? '';
		$data['billing_last_name']  = $parts[1] ?? '';
		if ( isset( $data['billing_phone'] ) ) {
			$data['billing_phone'] = priniti_core_normalize_mobile( (string) $data['billing_phone'] );
		}
		return $data;
	}
);

/** Signed-in customers: prefill the full name from their saved first and last name. */
add_filter(
	'woocommerce_checkout_get_value',
	static function ( $value, string $input ) {
		if ( 'billing_full_name' === $input && null === $value && WC()->customer ) {
			$name = trim( WC()->customer->get_billing_first_name() . ' ' . WC()->customer->get_billing_last_name() );
			return '' !== $name ? $name : $value;
		}
		return $value;
	},
	10,
	2
);

/** Same rules and messages as the reference's checkout form. */
add_action(
	'woocommerce_after_checkout_validation',
	static function ( array $data, WP_Error $errors ): void {
		if ( '' !== priniti_core_validate_name( (string) ( $data['billing_full_name'] ?? '' ) ) ) {
			$errors->add( 'billing_full_name_validation', __( 'Enter your full name.', 'priniti-core' ), array( 'id' => 'billing_full_name' ) );
		}
		if ( ! empty( $data['billing_phone'] ) && '' !== priniti_core_validate_mobile( (string) $data['billing_phone'] ) ) {
			$errors->add( 'billing_phone_validation', __( 'Enter a valid 10-digit mobile number.', 'priniti-core' ), array( 'id' => 'billing_phone' ) );
		}
		if ( ! empty( $data['billing_postcode'] ) && ! preg_match( '/^[1-9]\d{5}$/', (string) $data['billing_postcode'] ) ) {
			$errors->add( 'billing_postcode_validation', __( 'Enter a valid 6-digit pincode.', 'priniti-core' ), array( 'id' => 'billing_postcode' ) );
		}
	},
	10,
	2
);

/* ---- Quantity limit per cart line (UI and server agree) ---- */

add_filter(
	'woocommerce_quantity_input_max',
	static fn ( $max ) => ( '' === $max || $max < 0 || $max > PRINITI_CORE_MAX_QTY ) ? PRINITI_CORE_MAX_QTY : $max
);
add_filter( 'woocommerce_store_api_product_quantity_maximum', static fn ( $max ) => min( (int) $max, PRINITI_CORE_MAX_QTY ) );
add_filter(
	'woocommerce_add_to_cart_validation',
	static function ( $passed, $product_id, $quantity, $variation_id = 0 ) {
		if ( ! $passed || ! function_exists( 'WC' ) || ! WC()->cart ) {
			return $passed;
		}
		$target  = $variation_id ? (int) $variation_id : (int) $product_id;
		$in_cart = 0;
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( (int) ( $item['variation_id'] ?: $item['product_id'] ) === $target ) {
				$in_cart += (int) $item['quantity'];
			}
		}
		if ( $in_cart + (int) $quantity > PRINITI_CORE_MAX_QTY ) {
			/* translators: %d: maximum quantity per product */
			wc_add_notice( sprintf( __( 'You can add up to %d of each item.', 'priniti-core' ), PRINITI_CORE_MAX_QTY ), 'error' );
			return false;
		}
		return $passed;
	},
	10,
	4
);
