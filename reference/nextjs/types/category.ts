import type { ImageAsset } from "./product";

export interface Subcategory {
  slug: string;
  name: string;
}

export interface Category {
  slug: string;
  name: string;
  /** Pending until the business supplies copy; UI must tolerate it being absent. */
  description?: string;
  image?: ImageAsset;
  /** Slug of the product whose real pack image represents this category. */
  coverProductSlug?: string;
  subcategories?: Subcategory[];
  seo?: { title?: string; description?: string };
  /** 'official' = from the old Priniti site; 'ecommerce' = created for the online store only (e.g. Combos). */
  origin?: "official" | "ecommerce";
  /** false = route exists but the category is hidden from navigation, lists and search until it has products. */
  published?: boolean;
}
