import type { ReactNode } from "react";
import { getCartTotals } from "@/lib/cart";
import { formatINR } from "@/lib/format";
import { getLineTotal } from "@/lib/order";
import type { CartLine } from "@/types/cart";
import { ProductImage } from "./ProductImage";
import { TestPriceBadge } from "./TestPriceBadge";

const rowStyles = "flex items-baseline justify-between gap-4 text-sm";

interface OrderSummaryProps {
  lines: CartLine[];
  /** Checkout: list each item with variant, quantity, unit price and line total. */
  showLines?: boolean;
  /** Call-to-action area (checkout link, disabled place-order button, ...). */
  children?: ReactNode;
}

/**
 * The subtotal is the sum of the cart's line totals (quantity x unit price). Shipping, tax and the total are NOT
 * calculated: those rules are not configured, so they are labelled as such instead of being guessed.
 */
export function OrderSummary({ lines, showLines = false, children }: OrderSummaryProps) {
  const totals = getCartTotals(lines);
  const hasTest = lines.some((l) => l.isTestPrice);
  return (
    <div className="rounded-card border border-line bg-surface p-5 sm:p-6">
      <h2 className="font-display text-lg font-semibold">Order summary</h2>
      {hasTest ? (
        <p className="mt-2 rounded-lg bg-brand-tint px-3 py-2 text-xs font-semibold text-brand">TEST PRICES: these amounts are development placeholders, not real prices.</p>
      ) : null}

      {showLines ? (
        <ul role="list" className="mt-4 flex flex-col divide-y divide-line">
          {lines.map((l) => (
            <li key={`${l.productId}:${l.variantId}`} className="flex gap-3 py-3 first:pt-0">
              <div className="relative size-14 shrink-0 overflow-hidden rounded-lg border border-line bg-surface">
                <ProductImage image={l.image} name={l.name} sizes="56px" />
              </div>
              <div className="min-w-0 flex-1">
                <p className="line-clamp-2 text-sm font-semibold leading-snug">{l.name}</p>
                <p className="text-xs text-ink-soft">{l.variantLabel}</p>
                <p className="mt-0.5 flex flex-wrap items-center gap-x-2 text-xs text-ink-soft">
                  <span className="tabular-nums">
                    Qty {l.quantity} × {formatINR(l.price)}
                  </span>
                  {l.isTestPrice ? <TestPriceBadge /> : null}
                </p>
              </div>
              <p className="shrink-0 text-sm font-semibold tabular-nums">
                <span className="sr-only">Line total </span>
                {formatINR(getLineTotal(l))}
              </p>
            </li>
          ))}
        </ul>
      ) : null}

      <dl className={`flex flex-col gap-3 ${showLines ? "mt-3 border-t border-line pt-4" : "mt-4"}`}>
        <div className={rowStyles}>
          <dt className="text-ink-soft">
            Subtotal ({totals.itemCount} {totals.itemCount === 1 ? "item" : "items"})
          </dt>
          <dd className="font-semibold tabular-nums">{formatINR(totals.subtotal)}</dd>
        </div>
        {totals.savings > 0 ? (
          <div className={rowStyles}>
            <dt className="text-ink-soft">Savings on MRP</dt>
            <dd className="font-semibold tabular-nums text-leaf">{formatINR(totals.savings)}</dd>
          </div>
        ) : null}
        <div className={rowStyles}>
          <dt className="text-ink-soft">Shipping</dt>
          <dd className="text-right font-medium text-ink-soft">To be calculated</dd>
        </div>
        <div className={rowStyles}>
          <dt className="shrink-0 text-ink-soft">Tax (GST)</dt>
          <dd className="max-w-[12rem] text-right text-xs font-medium leading-snug text-ink-soft">To be confirmed before ecommerce launch</dd>
        </div>
        <div className={`${rowStyles} border-t border-line pt-3`}>
          <dt className="font-semibold">Total</dt>
          <dd className="font-display text-base font-bold text-ink-soft">Not final</dd>
        </div>
      </dl>
      <p className="mt-3 text-xs leading-relaxed text-ink-soft">
        This is a development checkout{hasTest ? " using TEST prices" : ""}. Shipping, tax and payment are not finalized, so the subtotal above is not a payable amount.
      </p>
      {children ? <div className="mt-5 flex flex-col gap-3">{children}</div> : null}
    </div>
  );
}
