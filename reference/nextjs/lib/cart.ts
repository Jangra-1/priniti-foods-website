import { siteConfig } from "@/data/site";
import type { CartLine } from "@/types/cart";

/**
 * Subtotal of real, priced cart lines only. Shipping, tax and the final total are NOT calculated:
 * those rules are not configured yet, so the UI must show them as pending instead of guessing.
 */
export function getCartTotals(lines: CartLine[]) {
  const itemCount = lines.reduce((n, l) => n + l.quantity, 0);
  const subtotal = lines.reduce((n, l) => n + l.price * l.quantity, 0);
  const mrpTotal = lines.reduce((n, l) => n + l.mrp * l.quantity, 0);
  const threshold = siteConfig.commerce.freeShippingThreshold;
  return {
    itemCount,
    subtotal,
    mrpTotal,
    savings: Math.max(0, mrpTotal - subtotal),
    /** undefined while free-shipping rules are unconfirmed */
    freeShippingRemaining: threshold ? Math.max(0, threshold - subtotal) : undefined,
    freeShippingProgress: threshold ? Math.min(1, subtotal / threshold) : undefined,
  };
}
