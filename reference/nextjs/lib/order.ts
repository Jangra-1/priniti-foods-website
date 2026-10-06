import type { CartLine } from "@/types/cart";
import type { CustomerInfo, ShippingAddress } from "@/types/checkout";
import type { DraftOrder, OrderItem } from "@/types/order";

/** variantId -> the price the catalog currently has. Built server-side from the same API the product pages use. */
export type PriceIndex = Record<string, { price: number; isTestPrice: boolean }>;

export const getLineTotal = (line: Pick<CartLine, "price" | "quantity">) => line.price * line.quantity;

/** Cart lines whose variant is gone from the catalog, or whose price no longer matches it. */
export function findStaleLines(lines: CartLine[], index: PriceIndex): CartLine[] {
  return lines.filter((l) => {
    const entry = index[l.variantId];
    return !entry || entry.price !== l.price;
  });
}

export function toOrderItems(lines: CartLine[]): OrderItem[] {
  return lines.map((l) => ({
    productId: l.productId,
    slug: l.slug,
    name: l.name,
    variantId: l.variantId,
    variantLabel: l.variantLabel,
    quantity: l.quantity,
    unitPrice: l.price,
    lineTotal: getLineTotal(l),
    isTestPrice: l.isTestPrice,
  }));
}

/**
 * Builds an in-memory order DRAFT from the cart and the checkout form. It is never stored or sent.
 * Shipping, tax and total are left unknown on purpose: those rules are not configured yet.
 */
export function buildDraftOrder(input: { lines: CartLine[]; customer: CustomerInfo; shippingAddress: ShippingAddress }): DraftOrder {
  const items = toOrderItems(input.lines);
  return {
    orderId: null,
    createdAt: null,
    persisted: false,
    customer: input.customer,
    shippingAddress: input.shippingAddress,
    items,
    subtotal: items.reduce((n, i) => n + i.lineTotal, 0),
    shipping: { status: "to-be-calculated", amount: null },
    tax: { status: "to-be-confirmed", amount: null },
    total: null,
    pricingMode: input.lines.some((l) => l.isTestPrice) ? "test" : "live",
    paymentStatus: "pending",
    orderStatus: "pending",
  };
}
