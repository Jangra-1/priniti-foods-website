import type { PackVariant, Product, ProductBadge } from "@/types/product";

export const badgeLabels: Record<ProductBadge, string> = {
  bestseller: "Best seller",
  new: "New",
  combo: "Combo",
};

export const hasPrice = (v: PackVariant): v is PackVariant & { price: number } => v.price !== undefined;

/** A variant that can be bought: it has a real price and is not known to be out of stock. */
export const isPurchasable = (v: PackVariant): v is PackVariant & { price: number } => hasPrice(v) && v.inStock !== false;

/** First purchasable variant, if any. */
export function getPurchasableVariant(product: Product) {
  return product.variants.find(isPurchasable);
}

/** Variant to show on cards: the first purchasable one, otherwise the first verified pack size. */
export function getDefaultVariant(product: Product): PackVariant | undefined {
  return getPurchasableVariant(product) ?? product.variants[0];
}

export function getDiscount(v: PackVariant | undefined): number {
  if (!v || v.price === undefined || v.mrp === undefined || v.mrp <= 0 || v.price >= v.mrp) return 0;
  return Math.round(((v.mrp - v.price) / v.mrp) * 100);
}

/** Names of details that are still pending for a product. Used for internal checks, never invented in the UI. */
export function getPendingFields(product: Product): string[] {
  const pending: string[] = [];
  if (!product.variants.length) pending.push("pack size");
  if (!product.variants.some(hasPrice)) pending.push("price");
  if (!product.description) pending.push("description");
  if (!product.ingredients) pending.push("ingredients");
  if (!product.nutrition?.length) pending.push("nutrition");
  if (!product.storage) pending.push("storage");
  return pending;
}
