import type { PackVariant, Product } from "@/types/product";

/**
 * DEVELOPMENT TEST PRICES. Not real Priniti Foods prices.
 * - applied only when testPricingEnabled is true (data/test-pricing.config.ts)
 * - never used as an MRP (no `mrp` is set) and never emitted in structured data
 * - products with no verified pack size get a placeholder variant labelled "Pack size TBC"
 * Delete this file (and its import in lib/api/products.ts) when real prices arrive.
 */
const CATEGORY_BASE: Record<string, number> = {
  "indian-traditional-namkeen": 40,
  "potato-chips": 20,
  "charchare-sticks": 10,
  popcorn: 20,
  "puffs-fryums": 10,
  "ringo-star-rings": 10,
  rusk: 60,
  sweets: 260,
  cookies: 60,
};

/** Price overrides for individual products. */
const PRODUCT_OVERRIDE: Record<string, number> = {
  "kaju-mixture": 120,
  "tasty-nuts": 60,
  panchrattan: 60,
  "navratan-mixture": 55,
  "diet-chiwda": 50,
  "diet-mixture": 50,
  "gathiya-papdi": 30,
  gathiya: 30,
};

/** Test prices for verified pack sizes (label -> price), where the size changes the price. */
const BY_PACK_LABEL: Record<string, Record<string, number>> = {
  cookies: { "200 g": 60, "300 g": 85 },
  sweets: { "1 Kg": 480, "400 g": 260 },
};

function priceFor(product: Product, label?: string): number {
  const byPack = label ? BY_PACK_LABEL[product.categorySlug]?.[label] : undefined;
  return byPack ?? PRODUCT_OVERRIDE[product.slug] ?? CATEGORY_BASE[product.categorySlug] ?? 50;
}

export function applyTestPricing(products: Product[]): Product[] {
  return products.map((p) => {
    const variants: PackVariant[] = p.variants.length
      ? p.variants.map((v) => ({ ...v, price: priceFor(p, v.label), isTestPrice: true }))
      : [
          {
            id: `${p.slug}-test-pack`,
            label: "Pack size TBC",
            source: "test-placeholder",
            price: priceFor(p),
            isTestPrice: true,
          },
        ];
    return { ...p, variants };
  });
}
