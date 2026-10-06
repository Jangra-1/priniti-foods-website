import { Bookmark, ShoppingBag } from "lucide-react";
import { useState } from "react";
import { config } from "@theme/config";
import { formatINR } from "@theme/lib/format";
import { saveForLater, useCartStore, useSavedStore } from "@theme/stores/cart";
import { ButtonLink, Button } from "./Button";
import { CartItem } from "./CartItem";

/**
 * Port of reference/nextjs/components/commerce/CartView.tsx + OrderSummary, on the WooCommerce cart.
 * Shipping, tax and the total are labelled the way the reference does until they are configured.
 */
const rowStyles = "flex items-baseline justify-between gap-4 text-sm";

function OrderSummary({ children }: { children: React.ReactNode }) {
  const lines = useCartStore((s) => s.lines);
  const itemCount = useCartStore((s) => s.itemCount);
  const subtotal = useCartStore((s) => s.subtotal);
  const coupons = useCartStore((s) => s.coupons);
  const removeCoupon = useCartStore((s) => s.removeCoupon);
  const savings = Math.max(0, lines.reduce((n, l) => n + (l.mrp - l.price) * l.quantity, 0));
  const { status } = config.commerce;
  const ready = status.shipping && status.tax && status.payment;

  return (
    <div className="rounded-card border border-line bg-surface p-5 sm:p-6">
      <h2 className="font-display text-lg font-semibold">Order summary</h2>
      <dl className="mt-4 flex flex-col gap-3">
        <div className={rowStyles}>
          <dt className="text-ink-soft">
            Subtotal ({itemCount} {itemCount === 1 ? "item" : "items"})
          </dt>
          <dd className="font-semibold tabular-nums">{formatINR(subtotal)}</dd>
        </div>
        {savings > 0 ? (
          <div className={rowStyles}>
            <dt className="text-ink-soft">Savings on MRP</dt>
            <dd className="font-semibold tabular-nums text-leaf">{formatINR(savings)}</dd>
          </div>
        ) : null}
        {coupons.map((c) => (
          <div key={c.code} className={rowStyles}>
            <dt className="text-ink-soft">
              Coupon {c.code.toUpperCase()}{" "}
              <button type="button" onClick={() => removeCoupon(c.code)} className="ml-1 text-xs font-medium text-ink-soft underline hover:text-brand">
                Remove
              </button>
            </dt>
            <dd className="font-semibold tabular-nums text-leaf">−{formatINR(c.discount)}</dd>
          </div>
        ))}
        <div className={rowStyles}>
          <dt className="text-ink-soft">Shipping</dt>
          <dd className="text-right font-medium text-ink-soft">{status.shipping ? "Calculated at checkout" : "To be calculated"}</dd>
        </div>
        <div className={rowStyles}>
          <dt className="shrink-0 text-ink-soft">Tax (GST)</dt>
          <dd className="max-w-[12rem] text-right text-xs font-medium leading-snug text-ink-soft">{status.tax ? (config.commerce.pricesIncludeTax ? "Included in prices" : "Calculated at checkout") : "To be confirmed before ecommerce launch"}</dd>
        </div>
        <div className={`${rowStyles} border-t border-line pt-3`}>
          <dt className="font-semibold">Total</dt>
          <dd className="font-display text-base font-bold text-ink-soft">{ready ? "Calculated at checkout" : "Not final"}</dd>
        </div>
      </dl>
      {!ready ? <p className="mt-3 text-xs leading-relaxed text-ink-soft">Shipping, tax and payment are not finalized yet, so the subtotal above is not a payable amount.</p> : null}
      <div className="mt-5 flex flex-col gap-3">{children}</div>
    </div>
  );
}

function CouponBox() {
  const applyCoupon = useCartStore((s) => s.applyCoupon);
  const [code, setCode] = useState("");
  if (!config.commerce.couponsEnabled) {
    return (
      <div className="mt-4 rounded-card border border-line bg-surface p-4 opacity-70">
        <div className="flex flex-col gap-1.5">
          <label htmlFor="coupon-code" className="text-sm font-medium">
            Coupon code
          </label>
          <input id="coupon-code" disabled placeholder="Not available yet" aria-describedby="coupon-hint" className="h-12 w-full rounded-xl border border-line bg-surface px-5 text-base placeholder:text-ink-soft/70" />
          <p id="coupon-hint" className="text-sm text-ink-soft">
            Coupons are not available yet.
          </p>
        </div>
      </div>
    );
  }
  return (
    <form
      className="mt-4 rounded-card border border-line bg-surface p-4"
      onSubmit={async (e) => {
        e.preventDefault();
        if (code.trim() && (await applyCoupon(code.trim()))) setCode("");
      }}
    >
      <label htmlFor="coupon-code" className="text-sm font-medium">
        Coupon code
      </label>
      <div className="mt-1.5 flex gap-2">
        <input id="coupon-code" value={code} onChange={(e) => setCode(e.target.value)} className="h-12 w-full rounded-xl border border-line bg-surface px-5 text-base focus:border-ink" />
        <Button type="submit" variant="secondary" className="h-12">
          Apply
        </Button>
      </div>
    </form>
  );
}

export function CartView() {
  const status = useCartStore((s) => s.status);
  const lines = useCartStore((s) => s.lines);
  const saved = useSavedStore((s) => s.saved);
  const removeSaved = useSavedStore((s) => s.remove);
  const { setQuantity, removeItem, clear, addItem } = useCartStore.getState();

  if (status === "idle" || (status === "loading" && lines.length === 0)) {
    return <div aria-busy="true" className="h-64 animate-pulse rounded-card bg-line/50" />;
  }

  return (
    <div className="flex flex-col gap-10">
      {lines.length === 0 ? (
        <div className="flex flex-col items-center gap-4 rounded-card border border-dashed border-line px-6 py-16 text-center">
          <span className="flex size-16 items-center justify-center rounded-full bg-brand-tint text-brand">
            <ShoppingBag className="size-7" aria-hidden />
          </span>
          <div>
            <p className="font-display text-xl font-semibold">Your cart is empty</p>
            <p className="mt-1 max-w-md text-ink-soft">Products can be added once their prices are published. You can browse the full range in the meantime.</p>
          </div>
          <ButtonLink href={config.urls.shop}>Continue shopping</ButtonLink>
        </div>
      ) : (
        <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-10">
          <section aria-labelledby="cart-items-heading">
            <div className="mb-2 flex items-center justify-between">
              <h2 id="cart-items-heading" className="font-display text-xl font-semibold">
                Items
              </h2>
              <button type="button" onClick={() => clear()} className="text-sm font-medium text-ink-soft hover:text-brand">
                Clear cart
              </button>
            </div>
            <ul role="list" className="flex flex-col divide-y divide-line rounded-card border border-line bg-surface px-4 sm:px-6">
              {lines.map((line) => (
                <li key={line.key} className="py-5">
                  <CartItem line={line} onQuantityChange={(q) => setQuantity(line.key, q)} onRemove={() => removeItem(line.key)} onSaveForLater={() => saveForLater(line)} />
                </li>
              ))}
            </ul>
          </section>

          <aside className="lg:sticky lg:top-28 lg:self-start">
            <OrderSummary>
              <ButtonLink href={config.urls.checkout} size="lg" fullWidth>
                Proceed to checkout
              </ButtonLink>
              <a href={config.urls.shop} className="text-center text-sm font-semibold text-brand underline-offset-4 hover:underline">
                Continue shopping
              </a>
            </OrderSummary>
            <CouponBox />
          </aside>
        </div>
      )}

      {saved.length > 0 ? (
        <section aria-labelledby="saved-heading">
          <h2 id="saved-heading" className="mb-2 flex items-center gap-2 font-display text-xl font-semibold">
            <Bookmark className="size-5" aria-hidden /> Saved for later
          </h2>
          <ul role="list" className="flex flex-col divide-y divide-line rounded-card border border-line bg-surface px-4 sm:px-6">
            {saved.map((line) => (
              <li key={`${line.id}:${line.variantLabel}`} className="flex flex-wrap items-center justify-between gap-3 py-4">
                <a href={line.href} className="font-display text-sm font-semibold hover:text-brand">
                  {line.name} {line.variantLabel ? <span className="font-normal text-ink-soft">({line.variantLabel})</span> : null}
                </a>
                <div className="flex gap-4 text-sm font-medium">
                  <button
                    type="button"
                    onClick={async () => {
                      if (await addItem({ id: line.id, quantity: line.quantity, variation: line.variation, name: line.name })) removeSaved(line.id, line.variantLabel);
                    }}
                    className="text-brand hover:underline"
                  >
                    Move to cart
                  </button>
                  <button type="button" onClick={() => removeSaved(line.id, line.variantLabel)} className="text-ink-soft hover:text-brand">
                    Remove
                  </button>
                </div>
              </li>
            ))}
          </ul>
        </section>
      ) : null}
    </div>
  );
}
