"use client";

import { Bookmark, ShoppingBag } from "lucide-react";
import Link from "next/link";
import { ButtonLink } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { useHydrated } from "@/hooks/useHydrated";
import { findStaleLines, type PriceIndex } from "@/lib/order";
import { lineKey, useCartStore } from "@/store/cart";
import { CartItem } from "./CartItem";
import { OrderSummary } from "./OrderSummary";

/** Full cart page. Lines only ever contain real, priced products (the store rejects anything else). */
export function CartView({ priceIndex }: { priceIndex?: PriceIndex }) {
  const hydrated = useHydrated();
  const storedLines = useCartStore((s) => s.lines);
  const storedSaved = useCartStore((s) => s.saved);
  const { setQuantity, removeItem, saveForLater, moveToCart, removeSaved, clear } = useCartStore.getState();

  const lines = hydrated ? storedLines : [];
  const saved = hydrated ? storedSaved : [];
  const stale = priceIndex ? findStaleLines(lines, priceIndex) : [];

  if (!hydrated) {
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
            <p className="mt-1 max-w-md text-ink-soft">
              Products can be added once their prices are published. You can browse the full range in the meantime.
            </p>
          </div>
          <ButtonLink href="/shop">Continue shopping</ButtonLink>
        </div>
      ) : (
        <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-10">
          <section aria-labelledby="cart-items-heading">
            {stale.length > 0 ? (
              <p role="alert" className="mb-3 rounded-xl border border-brand/30 bg-brand-tint px-4 py-3 text-sm">
                Some items no longer match the catalog (price or availability changed): {stale.map((l) => l.name).join(", ")}. Please remove and re-add them.
              </p>
            ) : null}
            <div className="mb-2 flex items-center justify-between">
              <h2 id="cart-items-heading" className="font-display text-xl font-semibold">
                Items
              </h2>
              <button type="button" onClick={clear} className="text-sm font-medium text-ink-soft hover:text-brand">
                Clear cart
              </button>
            </div>
            <ul role="list" className="flex flex-col divide-y divide-line rounded-card border border-line bg-surface px-4 sm:px-6">
              {lines.map((line) => {
                const key = lineKey(line);
                return (
                  <li key={key} className="py-5">
                    <CartItem
                      line={line}
                      onQuantityChange={(q) => setQuantity(key, q)}
                      onRemove={() => removeItem(key)}
                      onSaveForLater={() => saveForLater(key)}
                    />
                  </li>
                );
              })}
            </ul>
          </section>

          <aside className="lg:sticky lg:top-28 lg:self-start">
            <OrderSummary lines={lines}>
              <ButtonLink href="/checkout" size="lg" fullWidth>
                Proceed to checkout
              </ButtonLink>
              <Link href="/shop" className="text-center text-sm font-semibold text-brand underline-offset-4 hover:underline">
                Continue shopping
              </Link>
            </OrderSummary>
            <div className="mt-4 rounded-card border border-line bg-surface p-4 opacity-70">
              <Input label="Coupon code" placeholder="Not available yet" disabled hint="Coupons are not available yet." />
            </div>
          </aside>
        </div>
      )}

      {saved.length > 0 ? (
        <section aria-labelledby="saved-heading">
          <h2 id="saved-heading" className="mb-2 flex items-center gap-2 font-display text-xl font-semibold">
            <Bookmark className="size-5" aria-hidden /> Saved for later
          </h2>
          <ul role="list" className="flex flex-col divide-y divide-line rounded-card border border-line bg-surface px-4 sm:px-6">
            {saved.map((line) => {
              const key = lineKey(line);
              return (
                <li key={key} className="flex flex-wrap items-center justify-between gap-3 py-4">
                  <Link href={`/product/${line.slug}`} className="font-display text-sm font-semibold hover:text-brand">
                    {line.name} <span className="font-normal text-ink-soft">({line.variantLabel})</span>
                  </Link>
                  <div className="flex gap-4 text-sm font-medium">
                    <button type="button" onClick={() => moveToCart(key)} className="text-brand hover:underline">
                      Move to cart
                    </button>
                    <button type="button" onClick={() => removeSaved(key)} className="text-ink-soft hover:text-brand">
                      Remove
                    </button>
                  </div>
                </li>
              );
            })}
          </ul>
        </section>
      ) : null}
    </div>
  );
}
