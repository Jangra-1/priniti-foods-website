import type { CatalogCapabilities, CatalogFilters, Collection, SortKey } from "@/types/catalog";
import { formatINR } from "./format";

export const PAGE_SIZE = 8;

export const sortOptions: { value: SortKey; label: string }[] = [
  { value: "featured", label: "Default order" },
  { value: "name-asc", label: "Name: A to Z" },
  { value: "newest", label: "Newest first" },
  { value: "price-asc", label: "Price: low to high" },
  { value: "price-desc", label: "Price: high to low" },
  { value: "rating", label: "Top rated" },
];

export const priceRanges = [
  { value: "0-50", label: `Under ${formatINR(50)}` },
  { value: "50-100", label: `${formatINR(50)} to ${formatINR(100)}` },
  { value: "100-250", label: `${formatINR(100)} to ${formatINR(250)}` },
  { value: "250-", label: `Over ${formatINR(250)}` },
];

export const ratingOptions = [4, 3];

export type SearchParams = Record<string, string | string[] | undefined>;

const first = (v: string | string[] | undefined) => (Array.isArray(v) ? v[0] : v);
const isSort = (v?: string): v is SortKey => sortOptions.some((o) => o.value === v);
const isCollection = (v?: string): v is Collection => v === "best-sellers" || v === "new-arrivals";

export function parseFilters(sp: SearchParams): CatalogFilters {
  const sort = first(sp.sort);
  const collection = first(sp.collection);
  const price = first(sp.price);
  const rating = Number(first(sp.rating));
  const page = Math.floor(Number(first(sp.page)));
  return {
    q: (first(sp.q) ?? "").trim().slice(0, 80),
    categories: (first(sp.category) ?? "").split(",").filter(Boolean),
    price: priceRanges.some((r) => r.value === price) ? price : undefined,
    rating: ratingOptions.includes(rating) ? rating : undefined,
    inStock: first(sp.stock) === "1",
    sort: isSort(sort) ? sort : "featured",
    collection: isCollection(collection) ? collection : undefined,
    page: page >= 1 ? page : 1,
  };
}

/** Returns "" or "?a=b&c=d". Defaults are omitted so URLs stay clean and shareable. */
export function toQueryString(f: CatalogFilters): string {
  const p = new URLSearchParams();
  if (f.q) p.set("q", f.q);
  if (f.collection) p.set("collection", f.collection);
  if (f.categories.length) p.set("category", f.categories.join(","));
  if (f.price) p.set("price", f.price);
  if (f.rating) p.set("rating", String(f.rating));
  if (f.inStock) p.set("stock", "1");
  if (f.sort !== "featured") p.set("sort", f.sort);
  if (f.page > 1) p.set("page", String(f.page));
  const qs = p.toString();
  return qs ? `?${qs}` : "";
}

export function parsePriceRange(value?: string): { min: number; max: number } | null {
  if (!value) return null;
  const [min, max] = value.split("-");
  return { min: Number(min) || 0, max: max ? Number(max) : Infinity };
}

export function countActiveFilters(f: CatalogFilters, opts: { ignoreCategories?: boolean } = {}) {
  return (
    (opts.ignoreCategories ? 0 : f.categories.length) + (f.price ? 1 : 0) + (f.rating ? 1 : 0) + (f.inStock ? 1 : 0)
  );
}

export function getShopHeading(f: CatalogFilters) {
  if (f.q) return `Results for “${f.q}”`;
  if (f.collection === "best-sellers") return "Best sellers";
  if (f.collection === "new-arrivals") return "New arrivals";
  return "All snacks";
}

/** Sort options the current data can honour. Price/rating/newest only appear once the data exists. */
export function availableSortOptions(c: CatalogCapabilities) {
  return sortOptions.filter((o) => {
    if (o.value === "price-asc" || o.value === "price-desc") return c.prices;
    if (o.value === "rating") return c.ratings;
    if (o.value === "newest") return c.newFlags;
    return true;
  });
}

export const hasFilterGroups = (c: CatalogCapabilities, showCategories: boolean) =>
  showCategories || c.prices || c.ratings || c.stock;
