import { comboProducts } from "@/data/combos";
import { products as publishedProducts } from "@/data/products";
import { applyTestPricing } from "@/data/test-prices";
import { testPricingEnabled } from "@/data/test-pricing.config";
import { PAGE_SIZE, parsePriceRange } from "@/lib/catalog";
import type { PriceIndex } from "@/lib/order";
import { getDefaultVariant } from "@/lib/product";
import { matchesQuery } from "@/lib/search";
import type { CatalogCapabilities, CatalogFilters, ProductListResult, SearchIndexItem } from "@/types/catalog";
import type { Product } from "@/types/product";

/**
 * The storefront catalog: the 57 published products (+ combo products once any exist).
 * Pending products are never part of it. TEST prices are layered on only when the single
 * switch in data/test-pricing.config.ts is on; real data in data/products.ts is never modified.
 */
const catalogBase: Product[] = [...publishedProducts, ...comboProducts];
const products: Product[] = testPricingEnabled ? applyTestPricing(catalogBase) : catalogBase;

export interface ProductQuery {
  category?: string;
  collection?: "best-sellers" | "new-arrivals" | "featured" | "popular";
  search?: string;
  limit?: number;
}

// Swap these bodies for fetch() calls to the real backend later; signatures stay the same.
export async function getProducts(query: ProductQuery = {}): Promise<Product[]> {
  let result = products;
  if (query.category) result = result.filter((p) => p.categorySlug === query.category);
  if (query.collection === "best-sellers") result = result.filter((p) => p.badges?.includes("bestseller"));
  if (query.collection === "new-arrivals") result = result.filter((p) => p.badges?.includes("new"));
  if (query.collection === "featured") result = result.filter((p) => p.featured);
  if (query.collection === "popular") result = result.filter((p) => (p.reviewCount ?? 0) > 0).sort((a, b) => (b.reviewCount ?? 0) - (a.reviewCount ?? 0));
  if (query.search) result = result.filter((p) => matchesQuery(`${p.name} ${p.categoryName}`, query.search!));
  return query.limit ? result.slice(0, query.limit) : result;
}

export async function getProductBySlug(slug: string): Promise<Product | undefined> {
  return products.find((p) => p.slug === slug);
}

export async function getCategoryProductCounts(): Promise<Record<string, number>> {
  return products.reduce<Record<string, number>>((acc, p) => {
    acc[p.categorySlug] = (acc[p.categorySlug] ?? 0) + 1;
    return acc;
  }, {});
}

const priceOf = (p: Product) => getDefaultVariant(p)?.price;

/** Filter, sort and paginate. A backend would take the same CatalogFilters as query params. */
export async function getProductList(
  filters: CatalogFilters,
  opts: { lockedCategory?: string } = {},
): Promise<ProductListResult> {
  let list = [...products];

  if (opts.lockedCategory) list = list.filter((p) => p.categorySlug === opts.lockedCategory);
  else if (filters.categories.length) list = list.filter((p) => filters.categories.includes(p.categorySlug));

  if (filters.collection === "best-sellers") list = list.filter((p) => p.badges?.includes("bestseller"));
  if (filters.collection === "new-arrivals") list = list.filter((p) => p.badges?.includes("new"));

  if (filters.q) list = list.filter((p) => matchesQuery(`${p.name} ${p.categoryName}`, filters.q));

  const range = parsePriceRange(filters.price);
  if (range) {
    list = list.filter((p) => {
      const price = priceOf(p);
      return price !== undefined && price >= range.min && price < range.max;
    });
  }
  if (filters.rating) list = list.filter((p) => (p.rating ?? 0) >= filters.rating!);
  if (filters.inStock) list = list.filter((p) => p.variants.some((v) => v.inStock === true));

  switch (filters.sort) {
    case "name-asc":
      list.sort((a, b) => a.name.localeCompare(b.name, "en", { sensitivity: "base" }));
      break;
    case "price-asc":
      list.sort((a, b) => (priceOf(a) ?? Infinity) - (priceOf(b) ?? Infinity));
      break;
    case "price-desc":
      list.sort((a, b) => (priceOf(b) ?? -Infinity) - (priceOf(a) ?? -Infinity));
      break;
    case "rating":
      list.sort((a, b) => (b.rating ?? 0) - (a.rating ?? 0));
      break;
    case "newest":
      list.sort((a, b) => Number(!!b.badges?.includes("new")) - Number(!!a.badges?.includes("new")));
      break;
  }

  const total = list.length;
  const pageCount = Math.max(1, Math.ceil(total / PAGE_SIZE));
  const page = Math.min(filters.page, pageCount);
  return { items: list.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE), total, page, pageCount, pageSize: PAGE_SIZE };
}

/** Which filters/sorts the data can support right now (UI hides the rest instead of faking them). */
export async function getCatalogCapabilities(): Promise<CatalogCapabilities> {
  return {
    prices: products.some((p) => p.variants.some((v) => v.price !== undefined)),
    ratings: products.some((p) => (p.reviewCount ?? 0) > 0),
    stock: products.some((p) => p.variants.some((v) => v.inStock !== undefined)),
    newFlags: products.some((p) => p.badges?.includes("new")),
  };
}

/** Published products only. Used for header-search suggestions. */
export async function getSearchIndex(): Promise<SearchIndexItem[]> {
  return products.map((p) => ({
    slug: p.slug,
    name: p.name,
    categorySlug: p.categorySlug,
    categoryName: p.categoryName,
    image: p.images[0]?.src,
  }));
}

/** Same-category siblings first, then other published products, never the product itself. */
export async function getRelatedProducts(slug: string, limit = 4): Promise<Product[]> {
  const current = products.find((p) => p.slug === slug);
  if (!current) return [];
  const siblings = products.filter((p) => p.categorySlug === current.categorySlug && p.slug !== slug);
  const others = products.filter((p) => p.categorySlug !== current.categorySlug);
  return [...siblings, ...others].slice(0, limit);
}

export async function getPublishedSlugs(): Promise<string[]> {
  return products.map((p) => p.slug);
}

/** Products by slug, in the order given. Unknown/unpublished slugs are skipped (never throws). */
export async function getProductsBySlugs(slugs: string[]): Promise<Product[]> {
  return slugs.flatMap((s) => {
    const p = products.find((x) => x.slug === s);
    return p ? [p] : [];
  });
}

/** Current catalog price for every priced variant, used to flag stale cart lines at cart/checkout. */
export async function getPriceIndex(): Promise<PriceIndex> {
  const index: PriceIndex = {};
  for (const p of products) {
    for (const v of p.variants) {
      if (v.price !== undefined) index[v.id] = { price: v.price, isTestPrice: v.isTestPrice === true };
    }
  }
  return index;
}
