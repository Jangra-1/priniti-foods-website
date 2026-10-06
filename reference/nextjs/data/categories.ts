import type { Category } from "@/types/category";
import { comboProducts } from "./combos";

/**
 * Official Priniti Foods categories (from the old site's product structure).
 * Descriptions are PENDING (not invented). Cover images come from a real pack via coverProductSlug.
 * Combos is defined separately below as an ecommerce-only category (not an official Priniti category).
 */
const officialCategories: Category[] = [
  { slug: "indian-traditional-namkeen", name: "Indian Traditional Namkeen", coverProductSlug: "all-in-one" },
  { slug: "potato-chips", name: "Potato Chips", coverProductSlug: "chips-classic-salted" },
  { slug: "charchare-sticks", name: "CharChare Sticks", coverProductSlug: "charchare-mast-masala" },
  { slug: "popcorn", name: "Popcorn", coverProductSlug: "popcorn-butter-salted" },
  { slug: "puffs-fryums", name: "Puffs & Fryums", coverProductSlug: "jungle-masti" },
  { slug: "ringo-star-rings", name: "Ringo Star Rings", coverProductSlug: "ringo-star-tangy-tomato" },
  { slug: "rusk", name: "Rusk", coverProductSlug: "rusk" },
  { slug: "sweets", name: "Sweets", coverProductSlug: "gulab-jamun" },
  { slug: "cookies", name: "Cookies", coverProductSlug: "ajwain-cookies" },
  { slug: "donut-cakes", name: "Donut Cakes" }, // official category; products pending (no images supplied yet)
];

/**
 * Ecommerce-only categories (not part of the old site's structure). Combos stays unpublished,
 * and hidden from navigation and lists, until real combo products exist in data/combos.ts.
 */
const ecommerceCategories: Category[] = [
  { slug: "combos", name: "Combos", origin: "ecommerce", published: comboProducts.length > 0 },
];

export const categories: Category[] = [
  ...officialCategories.map((c) => ({ ...c, origin: "official" as const, published: true })),
  ...ecommerceCategories,
];
