<?php
/**
 * Meta keys shared by the plugin, the theme and the import tool (tools/import/src/meta-keys.ts mirrors this list).
 *
 * Product fields map to reference/nextjs/types/product.ts. All are optional: the storefront hides a field
 * that is empty and never invents content for it.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

final class Priniti_Meta {
	// Product (post meta on `product`).
	public const HIGHLIGHTS     = '_priniti_highlights';     // string[]       Product.highlights
	public const INGREDIENTS    = '_priniti_ingredients';    // string         Product.ingredients
	public const NUTRITION      = '_priniti_nutrition';      // {label, per100g}[] Product.nutrition
	public const STORAGE        = '_priniti_storage';        // string         Product.storage
	public const SHIPPING_NOTE  = '_priniti_shipping_note';  // string         Product.shippingNote
	public const FAQS           = '_priniti_faqs';           // {q, a}[]       Product.faqs
	public const PACK_SIZE      = '_priniti_pack_size';      // string         single verified pack size of a simple product, e.g. "400 g"
	public const PACK_SOURCE    = '_priniti_pack_source';    // string         PackSource: website|pack-art|image-filename (product or variation)
	public const SOURCE_ID      = '_priniti_source_id';      // string         reference id, e.g. "prn-aloo-bhujia" (import idempotency)
	public const INTERNAL_NOTES = '_priniti_internal_notes'; // string[]       data-quality notes: NEVER rendered on the storefront

	// Product category (term meta on `product_cat`).
	public const HIDE_WHEN_EMPTY = 'priniti_hide_when_empty'; // '1' = hidden from navigation and lists until it has products (Combos)
	public const ORIGIN          = 'priniti_origin';          // 'official' | 'ecommerce'
	public const COVER_PRODUCT   = 'priniti_cover_product';   // product slug whose pack image represents the category
}
