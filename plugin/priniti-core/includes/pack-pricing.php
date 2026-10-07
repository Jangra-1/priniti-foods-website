<?php
/**
 * E-commerce pack pricing (Ecomm Item List).
 *
 * Every sellable product or variation carries its sheet data:
 *   _priniti_mrp  MRP of one piece (from the sheet)
 *   _priniti_pcs  pieces in the sellable unit (1 = single pack; >1 = a predefined "Pack of X")
 * Its WooCommerce regular price is the price of one unit: MRP for single packs, MRP x Pcs for a Pack of X.
 *
 * Single packs (Pcs = 1) are bought as 1, 2 or 3 packs (cart quantity 1-3). Two or three packs cost
 * MRP x n x 0.88 (12% off); one pack costs MRP. The discount is applied to the cart line price here, so the
 * cart, checkout and order totals all agree with the product page. Pack-of-X units are never discounted.
 *
 * tools/import/src/ecomm/model.ts implements the same rule for the import plan and its tests.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

const PRINITI_CORE_MULTIPACK_DISCOUNT = 0.12;
const PRINITI_CORE_MULTIPACK_MAX      = 3;

/**
 * Pack data of a product or variation, or null when it has none (not in the e-commerce item list).
 *
 * @return array{mrp: float, pcs: int}|null
 */
function priniti_core_pack_info( $product ): ?array {
	if ( ! $product instanceof WC_Product ) {
		return null;
	}
	$id  = $product->get_id();
	$mrp = get_post_meta( $id, Priniti_Meta::MRP, true );
	$pcs = get_post_meta( $id, Priniti_Meta::PCS, true );
	if ( '' === $mrp || '' === $pcs || (float) $mrp <= 0 || (int) $pcs < 1 ) {
		return null;
	}
	return array(
		'mrp' => (float) $mrp,
		'pcs' => (int) $pcs,
	);
}

/** True for single packs sold with the 1/2/3 pack selector. */
function priniti_core_is_multipack_eligible( $product ): bool {
	$info = priniti_core_pack_info( $product );
	return $info && 1 === $info['pcs'];
}

/**
 * Price of one unit when `$quantity` units of a single pack are bought together.
 * MRP for one pack; MRP x 0.88 each for two or three packs. Pure: used by the cart hook and the local preview.
 */
function priniti_core_multipack_unit_price( float $mrp, int $quantity ): float {
	$quantity = max( 1, $quantity );
	return $quantity >= 2 && $quantity <= PRINITI_CORE_MULTIPACK_MAX ? round( $mrp * ( 1 - PRINITI_CORE_MULTIPACK_DISCOUNT ), 2 ) : $mrp;
}

/** Largest quantity one cart line may hold: 3 for single packs (the 1/2/3 selector), otherwise the site limit. */
function priniti_core_max_quantity( $product ): int {
	$max = priniti_core_is_multipack_eligible( $product ) ? PRINITI_CORE_MULTIPACK_MAX : PRINITI_CORE_MAX_QTY;
	return (int) apply_filters( 'priniti_core_max_quantity', $max, $product );
}

/* ---- Cart line prices ---- */

add_action(
	'woocommerce_before_calculate_totals',
	static function ( $cart ): void {
		if ( ! $cart instanceof WC_Cart || ( is_admin() && ! wp_doing_ajax() ) ) {
			return;
		}
		foreach ( $cart->get_cart() as $item ) {
			$product = $item['data'] ?? null;
			$info    = priniti_core_pack_info( $product );
			if ( ! $info || 1 !== $info['pcs'] ) {
				continue;
			}
			$product->set_price( priniti_core_multipack_unit_price( $info['mrp'], (int) $item['quantity'] ) );
		}
	},
	20
);

/* ---- Cart line and order details ---- */

add_filter(
	'woocommerce_get_item_data',
	static function ( array $item_data, array $cart_item ): array {
		$info = priniti_core_pack_info( $cart_item['data'] ?? null );
		$qty  = (int) ( $cart_item['quantity'] ?? 1 );
		if ( $info && 1 === $info['pcs'] && $qty >= 2 && $qty <= PRINITI_CORE_MULTIPACK_MAX ) {
			$item_data[] = array(
				'key'   => __( 'Offer', 'priniti-core' ),
				/* translators: %d: number of packs */
				'value' => sprintf( __( '%d packs, 12%% off', 'priniti-core' ), $qty ),
			);
		}
		return $item_data;
	},
	20,
	2
);

add_action(
	'woocommerce_checkout_create_order_line_item',
	static function ( WC_Order_Item_Product $item, string $cart_item_key, array $values ): void {
		$info = priniti_core_pack_info( $values['data'] ?? null );
		if ( ! $info ) {
			return;
		}
		$item->add_meta_data( '_priniti_mrp', $info['mrp'], true );
		$item->add_meta_data( '_priniti_pcs', $info['pcs'], true );
	},
	20,
	3
);
