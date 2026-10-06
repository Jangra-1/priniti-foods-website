/**
 * Loads the catalog from the design source of truth (reference/nextjs/data). Only PUBLISHED data is read:
 * data/pending-products.ts is never imported (same rule as the storefront, enforced by check:catalog there).
 * Development TEST prices (data/test-prices.ts) are never imported either.
 */
import { categories } from "../../../reference/nextjs/data/categories.ts";
import { comboProducts } from "../../../reference/nextjs/data/combos.ts";
import { products } from "../../../reference/nextjs/data/products.ts";
import type { Category } from "../../../reference/nextjs/types/category.ts";
import type { Product } from "../../../reference/nextjs/types/product.ts";

export const REFERENCE_PUBLIC_DIR = new URL("../../../reference/nextjs/public/", import.meta.url);

export function loadReferenceCatalog(): { categories: Category[]; products: Product[] } {
  return { categories, products: [...products, ...comboProducts] };
}
