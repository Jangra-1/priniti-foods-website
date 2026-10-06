import type { Product } from "@/types/product";

/**
 * COMBO PRODUCTS (ecommerce-only; Combos is NOT an official category of the old B2B site).
 *
 * Intentionally EMPTY: no real combo has been supplied, and none is invented.
 * To publish a combo later, add a Product here with `categorySlug: "combos"`, real images, a real name,
 * and `comboItems` listing the published products it contains. The Combos category switches from
 * "coming soon" to published automatically as soon as this array has at least one entry.
 */
export const comboProducts: Product[] = [];
