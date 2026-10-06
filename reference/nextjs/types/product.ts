export interface ImageAsset {
  src: string;
  alt: string;
}

export type ProductBadge = "bestseller" | "new" | "combo";

/** Where a pack size was verified. Never add a variant without one of these sources. */
export type PackSource = "website" | "pack-art" | "image-filename" | "test-placeholder";

/** One verified pack size. Price, MRP and SKU stay undefined until real data is supplied. */
export interface PackVariant {
  id: string;
  label: string; // e.g. "200 g"
  source: PackSource;
  mrp?: number; // rupees; undefined = pending
  price?: number; // rupees; undefined = pending
  sku?: string;
  inStock?: boolean; // undefined = no stock data
  /** TRUE = a development TEST price (not a real Priniti price, never an MRP, never in structured data). */
  isTestPrice?: boolean;
}

export interface NutritionRow {
  label: string;
  per100g: string;
}

export interface FAQ {
  q: string;
  a: string;
}

/**
 * Optional fields are PENDING until the business supplies them; UI must handle their absence
 * and never fill them with invented content.
 */
export interface Product {
  id: string;
  slug: string;
  name: string;
  categorySlug: string;
  categoryName: string; // denormalised so cards need no second lookup
  subcategory?: string;
  images: ImageAsset[];
  variants: PackVariant[]; // verified pack sizes only; empty = pack size pending
  rating?: number;
  reviewCount?: number;
  badges?: ProductBadge[];
  featured?: boolean;
  highlights?: string[];
  description?: string;
  ingredients?: string;
  nutrition?: NutritionRow[];
  storage?: string;
  shippingNote?: string;
  faqs?: FAQ[];
  relatedSlugs?: string[];
  boughtTogetherSlugs?: string[];
  seo?: { title?: string; description?: string };
  /** Combo packs only: the published products (and quantities) a combo contains. */
  comboItems?: { productSlug: string; quantity: number }[];
  /** Internal data-quality notes. Never rendered. */
  internalNotes?: string[];
  /** True only for sample rows used in development. */
  isMock?: boolean;
}
