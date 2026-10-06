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
