<?php
/**
 * Cart line details.
 *
 * Simple products with one verified pack size store it in _priniti_pack_size. It is shown on cart lines,
 * the checkout and orders as "Pack size" (variable products already show their Pack size attribute),
 * matching the reference cart's variant label.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'woocommerce_get_item_data',
	static function ( array $item_data, array $cart_item ): array {
		$product = $cart_item['data'] ?? null;
		if ( ! $product instanceof WC_Product || $product->is_type( 'variation' ) ) {
			return $item_data;
		}
		$pack = (string) get_post_meta( $product->get_id(), Priniti_Meta::PACK_SIZE, true );
		if ( '' !== $pack ) {
			$item_data[] = array(
				'key'   => __( 'Pack size', 'priniti-core' ),
				'value' => $pack,
			);
		}
		return $item_data;
	},
	10,
	2
);

add_action(
	'woocommerce_checkout_create_order_line_item',
	static function ( WC_Order_Item_Product $item, string $cart_item_key, array $values ): void {
		$product = $values['data'] ?? null;
		if ( ! $product instanceof WC_Product || $product->is_type( 'variation' ) ) {
			return;
		}
		$pack = (string) get_post_meta( $product->get_id(), Priniti_Meta::PACK_SIZE, true );
		if ( '' !== $pack ) {
			$item->add_meta_data( __( 'Pack size', 'priniti-core' ), $pack, true );
		}
	},
	10,
	3
);
