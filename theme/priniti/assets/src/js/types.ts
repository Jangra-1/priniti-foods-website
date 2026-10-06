/**
 * A cart line as the UI renders it (reference/nextjs/types/cart.ts, adapted to WooCommerce):
 * WooCommerce owns the cart and its prices; these lines are a read model of the Store API cart.
 */
export interface CartLine {
  /** WooCommerce cart item key */
  key: string;
  /** Product or variation id */
  id: number;
  name: string;
  href: string;
  image?: { src: string; alt: string };
  variantLabel: string;
  variation: { attribute: string; value: string }[];
  /** Unit price and unit MRP (regular price), in rupees */
  price: number;
  mrp: number;
  quantity: number;
  maxQuantity: number;
}

/** Product data for the quick-view dialog (priniti_product_payload() in inc/components/commerce.php). */
export interface QuickViewVariant {
  id: number;
  label: string;
  source: string;
  price: number | null;
  mrp: number | null;
  attributes: { attribute: string; value: string }[];
  purchasable: boolean;
}

export interface QuickViewProduct {
  id: number;
  name: string;
  href: string;
  categoryName: string;
  categoryHref: string;
  image: { src: string; alt: string; srcset?: string } | null;
  variants: QuickViewVariant[];
}
