import type { Product } from "./product";

export type SortKey = "featured" | "name-asc" | "price-asc" | "price-desc" | "rating" | "newest";
export type Collection = "best-sellers" | "new-arrivals";

/** Everything the shop/category pages can filter on. Mirrors the URL query string. */
export interface CatalogFilters {
  q: string;
  categories: string[];
  price?: string; // one of priceRanges[].value, e.g. "50-100"
  rating?: number; // minimum rating
  inStock: boolean;
  sort: SortKey;
  collection?: Collection;
  page: number;
}

export interface ProductListResult {
  items: Product[];
  total: number;
  page: number;
  pageCount: number;
  pageSize: number;
}

/** What the current catalog data can support. Filters and sorts without data are hidden, never faked. */
export interface CatalogCapabilities {
  prices: boolean;
  ratings: boolean;
  stock: boolean;
  newFlags: boolean;
}

/** Lightweight published-product entry for client-side search suggestions. */
export interface SearchIndexItem {
  slug: string;
  name: string;
  categorySlug: string;
  categoryName: string;
  image?: string;
}
