import type { Category } from "../../../reference/nextjs/types/category.ts";
import type { Product } from "../../../reference/nextjs/types/product.ts";
import { META, PACK_SIZE_ATTRIBUTE, TERM_META } from "./meta-keys.ts";

/**
 * Pure mapping from the reference data model to WooCommerce REST payloads. No I/O here.
 *
 * Rules (from reference/nextjs/README.md, "never invent data"):
 * - price/MRP/SKU/stock are only sent when the reference has a real value (none do yet, so products import
 *   without a price: WooCommerce then treats them as not purchasable, i.e. "Price coming soon").
 * - 0 or 1 verified pack size -> simple product (the single size goes to _priniti_pack_size);
 *   2+ verified pack sizes   -> variable product with the global "Pack size" attribute.
 * - Products start as drafts; publishing is a separate, explicit step.
 */

export interface PlannedImage {
  /** Path under reference/nextjs/public, e.g. images/products/aloo-bhujia-1.webp */
  file: string;
  alt: string;
}

export interface PlannedCategory {
  slug: string;
  name: string;
  menu_order: number;
  description?: string;
  coverImage?: PlannedImage;
  meta: Record<string, string>;
}

export interface PlannedVariation {
  packSize: string;
  regular_price?: string;
  sale_price?: string;
  sku?: string;
  stock_status?: "instock" | "outofstock";
  meta_data: { key: string; value: unknown }[];
}

export interface PlannedProduct {
  sourceId: string;
  slug: string;
  name: string;
  type: "simple" | "variable";
  status: "draft";
  categorySlug: string;
  images: PlannedImage[];
  description?: string;
  regular_price?: string;
  sale_price?: string;
  sku?: string;
  stock_status?: "instock" | "outofstock";
  featured: boolean;
  tags: string[];
  attributes: { name: string; slug: string; variation: boolean; visible: boolean; options: string[] }[];
  variations: PlannedVariation[];
  meta_data: { key: string; value: unknown }[];
}

const money = (n?: number) => (n === undefined ? undefined : n.toFixed(2));
const imagePath = (src: string) => src.replace(/^\//, "");

/** WooCommerce stores MRP as the regular price and the selling price as the sale price. */
function prices(v: { mrp?: number; price?: number; isTestPrice?: boolean }) {
  if (v.isTestPrice || v.price === undefined) return {};
  const mrp = v.mrp ?? v.price;
  return mrp > v.price ? { regular_price: money(mrp), sale_price: money(v.price) } : { regular_price: money(v.price) };
}

const stock = (inStock?: boolean) => (inStock === undefined ? undefined : inStock ? ("instock" as const) : ("outofstock" as const));

export function mapCategory(c: Category, index: number, products: Product[]): PlannedCategory {
  const cover = c.image ?? products.find((p) => p.slug === c.coverProductSlug)?.images[0];
  return {
    slug: c.slug,
    name: c.name,
    menu_order: index,
    ...(c.description ? { description: c.description } : {}),
    ...(cover ? { coverImage: { file: imagePath(cover.src), alt: cover.alt } } : {}),
    meta: {
      [TERM_META.origin]: c.origin ?? "official",
      [TERM_META.hideWhenEmpty]: c.published === false || c.origin === "ecommerce" ? "1" : "",
      ...(c.coverProductSlug ? { [TERM_META.coverProduct]: c.coverProductSlug } : {}),
    },
  };
}

export function mapProduct(p: Product): PlannedProduct {
  const verified = p.variants.filter((v) => v.source !== "test-placeholder");
  const variable = verified.length > 1;
  const single = verified.length === 1 ? verified[0] : undefined;

  const meta: { key: string; value: unknown }[] = [{ key: META.sourceId, value: p.id }];
  const add = (key: string, value: unknown) => {
    if (value !== undefined && !(Array.isArray(value) && value.length === 0)) meta.push({ key, value });
  };
  add(META.highlights, p.highlights);
  add(META.ingredients, p.ingredients);
  add(META.nutrition, p.nutrition);
  add(META.storage, p.storage);
  add(META.shippingNote, p.shippingNote);
  add(META.faqs, p.faqs);
  add(META.internalNotes, p.internalNotes);
  if (single) {
    add(META.packSize, single.label);
    add(META.packSource, single.source);
  }

  const tags = [...(p.badges ?? [])].filter((b) => b !== "combo");

  return {
    sourceId: p.id,
    slug: p.slug,
    name: p.name,
    type: variable ? "variable" : "simple",
    status: "draft",
    categorySlug: p.categorySlug,
    images: p.images.map((i) => ({ file: imagePath(i.src), alt: i.alt })),
    ...(p.description ? { description: p.description } : {}),
    ...(single ? { ...prices(single), ...(single.sku ? { sku: single.sku } : {}), ...(stock(single.inStock) ? { stock_status: stock(single.inStock) } : {}) } : {}),
    featured: p.featured === true,
    tags,
    attributes: variable
      ? [{ name: PACK_SIZE_ATTRIBUTE.name, slug: PACK_SIZE_ATTRIBUTE.slug, variation: true, visible: true, options: verified.map((v) => v.label) }]
      : [],
    variations: variable
      ? verified.map((v) => ({
          packSize: v.label,
          ...prices(v),
          ...(v.sku ? { sku: v.sku } : {}),
          ...(stock(v.inStock) ? { stock_status: stock(v.inStock) } : {}),
          meta_data: [{ key: META.packSource, value: v.source }],
        }))
      : [],
    meta_data: meta,
  };
}
