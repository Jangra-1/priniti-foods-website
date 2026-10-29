<?php
/**
 * Builds the shipment object for iThink's Add Order call from a plain order array, so it can be tested without
 * WooCommerce (Priniti_IThink_Orders::order_data() makes that array from a WC_Order).
 *
 * Field names and string values follow the documented sample (doc-add-order/3): numbers are sent as strings,
 * order_date as DD-MM-YYYY, dimensions in cm. The weight unit is NOT assumed: the docs say kg but their sample
 * weight is 400, so PRINITI_ITHINK_WEIGHT_UNIT must be confirmed with iThink and set before anything is sent.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

final class Priniti_IThink_Payload {
	public const MAX_PRODUCTS = 40;

	/**
	 * @param array{
	 *   number: string, date: DateTimeInterface, total: float, payment: string,
	 *   name: string, address_1: string, address_2: string, city: string, state: string, pincode: string,
	 *   phone: string, email: string, shipping_total: float, discount_total: float,
	 *   items: array<int, array{name: string, sku: string, qty: int, unit_price: float}>
	 * } $order
	 * @param array{weight_g: int, length_cm: float, width_cm: float, height_cm: float} $package
	 */
	public static function shipment( array $order, array $package, Priniti_IThink_Config $config ): array {
		$problems = array_merge( $config->shipment_problems(), self::order_problems( $order ), self::package_problems( $package ) );
		if ( $problems ) {
			throw new Priniti_IThink_Error( 'validation', 'The shipment cannot be created yet: ' . implode( ' ', $problems ) );
		}

		$cod      = 'cod' === strtolower( (string) $order['payment'] );
		$total    = (float) $order['total'];
		$products = array();
		foreach ( $order['items'] as $item ) {
			$products[] = array(
				'product_name'     => self::text( $item['name'], 200 ),
				'product_sku'      => self::text( (string) ( $item['sku'] ?? '' ), 100 ),
				'product_quantity' => (string) (int) $item['qty'],
				'product_price'    => Priniti_IThink_Client::money( (float) $item['unit_price'] ),
			);
		}

		$name    = self::text( $order['name'], 100 );
		$add     = self::text( $order['address_1'], 250 );
		$add2    = self::text( (string) ( $order['address_2'] ?? '' ), 250 );
		$city    = self::text( $order['city'], 100 );
		$state   = self::text( $order['state'], 100 );
		$pin     = (string) $order['pincode'];
		$phone   = self::mobile( (string) $order['phone'] );
		$email   = self::text( (string) ( $order['email'] ?? '' ), 150 );

		$shipment = array(
			'order'                       => self::text( (string) $order['number'], 50 ),
			'order_date'                  => $order['date']->format( 'd-m-Y' ),
			'total_amount'                => Priniti_IThink_Client::money( $total ),
			'name'                        => $name,
			'add'                         => $add,
			'add2'                        => $add2,
			'pin'                         => $pin,
			'city'                        => $city,
			'state'                       => $state,
			'country'                     => 'India',
			'phone'                       => $phone,
			'email'                       => $email,
			'is_billing_same_as_shipping' => 'yes',
			'billing_name'                => $name,
			'billing_add'                 => $add,
			'billing_add2'                => $add2,
			'billing_pin'                 => $pin,
			'billing_city'                => $city,
			'billing_state'               => $state,
			'billing_country'             => 'India',
			'billing_phone'               => $phone,
			'billing_email'               => $email,
			'products'                    => $products,
			'shipment_length'             => Priniti_IThink_Client::number( (float) $package['length_cm'] ),
			'shipment_width'              => Priniti_IThink_Client::number( (float) $package['width_cm'] ),
			'shipment_height'             => Priniti_IThink_Client::number( (float) $package['height_cm'] ),
			'weight'                      => self::weight( (int) $package['weight_g'], $config->weight_unit() ),
			'shipping_charges'            => Priniti_IThink_Client::money( (float) ( $order['shipping_total'] ?? 0 ) ),
			'total_discount'              => Priniti_IThink_Client::money( (float) ( $order['discount_total'] ?? 0 ) ),
			'cod_amount'                  => $cod ? Priniti_IThink_Client::money( $total ) : '0',
			'payment_mode'                => Priniti_IThink_Client::payment_label( $cod ? 'cod' : 'prepaid' ),
			'return_address_id'           => (string) $config->return_address_id(),
		);
		if ( $config->store_id() ) {
			$shipment['store_id'] = (string) $config->store_id();
		}
		return $shipment;
	}

	/** Grams in the configured unit, exact: 450 g => "0.45" (kg) or "450" (g). */
	public static function weight( int $grams, string $unit ): string {
		return 'g' === $unit ? (string) $grams : Priniti_IThink_Client::grams_to_kg( $grams );
	}

	/** @return string[] */
	public static function order_problems( array $order ): array {
		$p = array();
		foreach ( array( 'number' => 'order number', 'name' => 'customer name', 'address_1' => 'address', 'city' => 'city', 'state' => 'state' ) as $key => $label ) {
			if ( '' === trim( (string) ( $order[ $key ] ?? '' ) ) ) {
				$p[] = "The {$label} is missing.";
			}
		}
		if ( ! ( ( $order['date'] ?? null ) instanceof DateTimeInterface ) ) {
			$p[] = 'The order date is missing.';
		}
		if ( ! preg_match( '/^[1-9]\d{5}$/', (string) ( $order['pincode'] ?? '' ) ) ) {
			$p[] = 'The pincode is not a valid 6-digit pincode.';
		}
		if ( '' === self::mobile( (string) ( $order['phone'] ?? '' ) ) ) {
			$p[] = 'The mobile number is not a valid 10-digit Indian mobile number.';
		}
		if ( (float) ( $order['total'] ?? 0 ) <= 0 ) {
			$p[] = 'The order total is zero.';
		}
		$items = $order['items'] ?? array();
		if ( ! $items ) {
			$p[] = 'The order has no products.';
		} elseif ( count( $items ) > self::MAX_PRODUCTS ) {
			$p[] = 'iThink accepts at most ' . self::MAX_PRODUCTS . ' products per shipment.';
		}
		foreach ( $items as $item ) {
			if ( (int) ( $item['qty'] ?? 0 ) < 1 || '' === trim( (string) ( $item['name'] ?? '' ) ) ) {
				$p[] = 'A product line has no name or quantity.';
				break;
			}
		}
		return $p;
	}

	/** @return string[] */
	public static function package_problems( array $package ): array {
		$p = array();
		if ( (int) ( $package['weight_g'] ?? 0 ) <= 0 ) {
			$p[] = 'The parcel weight (g) is missing.';
		}
		foreach ( array( 'length_cm' => 'length', 'width_cm' => 'width', 'height_cm' => 'height' ) as $key => $label ) {
			if ( (float) ( $package[ $key ] ?? 0 ) <= 0 ) {
				$p[] = "The parcel {$label} (cm) is missing.";
			}
		}
		return $p;
	}

	/** 10-digit Indian mobile number, or '' (accepts +91 / 0 prefixes and spaces). */
	public static function mobile( string $phone ): string {
		$digits = (string) preg_replace( '/\D/', '', $phone );
		if ( 12 === strlen( $digits ) && str_starts_with( $digits, '91' ) ) {
			$digits = substr( $digits, 2 );
		} elseif ( 11 === strlen( $digits ) && str_starts_with( $digits, '0' ) ) {
			$digits = substr( $digits, 1 );
		}
		return preg_match( '/^[6-9]\d{9}$/', $digits ) ? $digits : '';
	}

	private static function text( string $value, int $max ): string {
		$value = trim( (string) preg_replace( '/\s+/u', ' ', strip_tags( $value ) ) );
		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $max ) : substr( $value, 0, $max );
	}
}
