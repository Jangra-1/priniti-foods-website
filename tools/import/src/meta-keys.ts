/** Mirrors plugin/priniti-core/includes/meta-keys.php. Keep both in sync. */
export const META = {
  highlights: "_priniti_highlights",
  ingredients: "_priniti_ingredients",
  nutrition: "_priniti_nutrition",
  storage: "_priniti_storage",
  shippingNote: "_priniti_shipping_note",
  faqs: "_priniti_faqs",
  packSize: "_priniti_pack_size",
  packSource: "_priniti_pack_source",
  sourceId: "_priniti_source_id",
  internalNotes: "_priniti_internal_notes",
  // Sellable unit (product or variation), from the e-commerce item list.
  mrp: "_priniti_mrp",
  pcs: "_priniti_pcs",
  weight: "_priniti_weight",
  ecommItem: "_priniti_ecomm_item",
} as const;

export const TERM_META = {
  hideWhenEmpty: "priniti_hide_when_empty",
  origin: "priniti_origin",
  coverProduct: "priniti_cover_product",
} as const;

/** Global attribute used for products sold in more than one pack size. */
export const PACK_SIZE_ATTRIBUTE = { name: "Pack size", slug: "pack-size" } as const;
