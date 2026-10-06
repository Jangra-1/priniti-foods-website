"use client";

import { TriangleAlert } from "lucide-react";
import Link from "next/link";
import { OrderSummary } from "@/components/commerce/OrderSummary";
import { Button, ButtonLink } from "@/components/ui/Button";
import { useHydrated } from "@/hooks/useHydrated";
import { findStaleLines, type PriceIndex } from "@/lib/order";
import { useCartStore } from "@/store/cart";
import { CheckoutForm } from "./CheckoutForm";
import { IntegrationStatus } from "./IntegrationStatus";

export function CheckoutView({ priceIndex }: { priceIndex: PriceIndex }) {
  const hydrated = useHydrated();
  const storedLines = useCartStore((s) => s.lines);
  const lines = hydrated ? storedLines : [];
  const stale = findStaleLines(lines, priceIndex);

  return (
    <div className="flex flex-col gap-8">
      <IntegrationStatus />

      {!hydrated ? (
        <div aria-busy="true" className="h-64 animate-pulse rounded-card bg-line/50" />
      ) : lines.length === 0 ? (
        <div className="flex flex-col items-center gap-4 rounded-card border border-dashed border-line px-6 py-14 text-center">
          <p className="font-display text-xl font-semibold">Your cart is empty</p>
          <p className="max-w-md text-ink-soft">There is nothing to check out yet. Add products to your cart first.</p>
          <ButtonLink href="/shop">Back to shop</ButtonLink>
        </div>
      ) : (
        <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_24rem] lg:gap-10">
          <div className="order-2 flex flex-col gap-6 lg:order-1">
            {stale.length > 0 ? (
              <div role="alert" className="flex items-start gap-3 rounded-xl border border-brand/30 bg-brand-tint p-4 text-sm">
                <TriangleAlert className="mt-0.5 size-5 shrink-0 text-brand" aria-hidden />
                <p className="leading-relaxed">
                  <strong className="font-semibold">Some cart items no longer match the catalog</strong> (their price or availability changed): {stale.map((l) => `${l.name} (${l.variantLabel})`).join(", ")}.{" "}
                  <Link href="/cart" className="font-semibold text-brand underline-offset-4 hover:underline">
                    Review your cart
                  </Link>{" "}
                  and re-add them.
                </p>
              </div>
            ) : null}
            <CheckoutForm lines={lines} />
          </div>
          <aside className="order-1 lg:order-2 lg:sticky lg:top-28 lg:self-start">
            <OrderSummary lines={lines} showLines>
              <Button size="lg" fullWidth disabled aria-describedby="place-order-note">
                Payment integration coming next
              </Button>
              <p id="place-order-note" className="text-xs text-ink-soft">
                Placing an order is switched off. No order is created and no payment is taken until the integrations listed above are in place.
              </p>
              <Link href="/cart" className="text-center text-sm font-semibold text-brand underline-offset-4 hover:underline">
                Back to cart
              </Link>
            </OrderSummary>
          </aside>
        </div>
      )}
    </div>
  );
}
