import type { ImageAsset } from "./product";

/**
 * A cart line snapshots display data (name, price) so the UI works offline.
 * When the backend arrives, prices must be re-validated server-side.
 */
export interface CartLine {
  productId: string;
  slug: string;
  name: string;
  variantId: string;
  variantLabel: string;
  image?: ImageAsset;
  price: number;
  mrp: number;
  quantity: number;
  /** Price was a development TEST price. Such lines are dropped if test pricing is switched off. */
  isTestPrice?: boolean;
}
