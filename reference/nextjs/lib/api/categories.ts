import { categories } from "@/data/categories";
import { products } from "@/data/products";
import type { Category } from "@/types/category";

/** Attaches each category's cover image from a real product pack (never a generated image). */
function withCover(c: Category): Category {
  if (c.image || !c.coverProductSlug) return c;
  const cover = products.find((p) => p.slug === c.coverProductSlug);
  return cover?.images[0] ? { ...c, image: cover.images[0] } : c;
}

// Swap these bodies for fetch() calls to the real backend later; signatures stay the same.
/** Published categories only (navigation, filters, homepage, search suggestions). */
export async function getCategories(): Promise<Category[]> {
  return categories.filter((c) => c.published !== false).map(withCover);
}

/** Every category route, including unpublished ones (they render a "coming soon" page). */
export async function getAllCategorySlugs(): Promise<string[]> {
  return categories.map((c) => c.slug);
}

export async function getCategoryBySlug(slug: string): Promise<Category | undefined> {
  const c = categories.find((x) => x.slug === slug);
  return c ? withCover(c) : undefined;
}
