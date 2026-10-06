import type { CustomerInfo, ShippingAddress } from "./checkout";

export type PaymentStatus = "pending" | "paid" | "failed";
export type OrderStatus = "pending" | "confirmed" | "packed" | "shipped" | "delivered" | "cancelled";

export interface OrderItem {
  productId: string;
  slug: string;
  name: string;
  variantId: string;
  variantLabel: string;
  quantity: number;
  unitPrice: number;
  lineTotal: number;
  /** The unit price was a development TEST price. */
  isTestPrice?: boolean;
}

/** A charge that may not be known yet: shipping and tax are NOT configured, so `amount` is null until they are. */
export interface OrderAmount {
  status: "to-be-calculated" | "to-be-confirmed" | "calculated";
  amount: number | null;
}

/**
 * Order model for the future backend. Nothing creates or stores real orders yet.
 * `orderId` and `createdAt` are assigned by the backend, never by the browser.
 */
export interface Order {
  orderId: string;
  customer: CustomerInfo;
  shippingAddress: ShippingAddress;
  items: OrderItem[];
  subtotal: number;
  shipping: OrderAmount;
  tax: OrderAmount;
  /** null until shipping and tax are configured: the total is never guessed. */
  total: number | null;
  /** "test" while any line uses a development TEST price. */
  pricingMode: "test" | "live";
  paymentStatus: PaymentStatus;
  orderStatus: OrderStatus;
  createdAt: string;
}

/** An in-memory draft: no id, no timestamp, never persisted. */
export type DraftOrder = Omit<Order, "orderId" | "createdAt"> & {
  orderId: null;
  createdAt: null;
  persisted: false;
};
