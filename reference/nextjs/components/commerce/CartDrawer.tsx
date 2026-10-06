"use client";

import { ShoppingBag } from "lucide-react";
import { Drawer } from "@/components/ui/Drawer";
import { ButtonLink } from "@/components/ui/Button";
import { useHydrated } from "@/hooks/useHydrated";
import { getCartTotals } from "@/lib/cart";
import { formatINR } from "@/lib/format";
import { lineKey, useCartStore } from "@/store/cart";
import { useUIStore } from "@/store/ui";
import { CartItem } from "./CartItem";

export function CartDrawer() {
  const open = useUIStore((s) => s.cartOpen);
  const close = useUIStore((s) => s.closeCart);
  const hydrated = useHydrated();
  const storedLines = useCartStore((s) => s.lines);
  const { setQuantity, removeItem, saveForLater } = useCartStore.getState();

  const lines = hydrated ? storedLines : [];
  const totals = getCartTotals(lines);

  const footer = lines.length ? (
    <div className="flex flex-col gap-3">
      <div className="flex items-baseline justify-between">
        <span className="text-sm text-ink-soft">Subtotal</span>
        <span className="font-display text-xl font-bold tabular-nums">{formatINR(totals.subtotal)}</span>
      </div>
      {lines.some((l) => l.isTestPrice) ? <p className="text-xs font-semibold text-brand">TEST PRICES: not real Priniti prices.</p> : null}
      {totals.savings > 0 ? <p className="text-sm font-medium text-leaf">You save {formatINR(totals.savings)} on MRP</p> : null}
      <p className="text-xs text-ink-soft">Shipping and tax are not included in this subtotal.</p>
      <div className="grid grid-cols-2 gap-2">
        <ButtonLink href="/cart" variant="secondary" onClick={close}>
          View cart
        </ButtonLink>
        <ButtonLink href="/checkout" onClick={close}>
          Checkout
        </ButtonLink>
      </div>
    </div>
  ) : null;

  return (
    <Drawer open={open} onClose={close} title={`Cart${totals.itemCount ? ` (${totals.itemCount})` : ""}`} footer={footer}>
      {lines.length === 0 ? (
        <div className="flex h-full flex-col items-center justify-center gap-4 px-8 py-16 text-center">
          <span className="flex size-16 items-center justify-center rounded-full bg-brand-tint text-brand">
            <ShoppingBag className="size-7" aria-hidden />
          </span>
          <div>
            <p className="font-display text-lg font-semibold">Your cart is empty</p>
            <p className="mt-1 text-sm text-ink-soft">Add a snack or two and they will show up here.</p>
          </div>
          <ButtonLink href="/shop" onClick={close}>
            Start shopping
          </ButtonLink>
        </div>
      ) : (
        <div className="flex flex-col">
          {totals.freeShippingRemaining !== undefined && totals.freeShippingProgress !== undefined ? (
          <div className="border-b border-line bg-canvas px-5 py-3">
            {totals.freeShippingRemaining > 0 ? (
              <p className="text-sm">
                Add <strong>{formatINR(totals.freeShippingRemaining)}</strong> more for free shipping
              </p>
            ) : (
              <p className="text-sm font-medium text-leaf">You have unlocked free shipping</p>
            )}
            <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-line" role="presentation">
              <div className="h-full rounded-full bg-leaf transition-[width] duration-500" style={{ width: `${totals.freeShippingProgress * 100}%` }} />
            </div>
          </div>
          ) : null}
          <ul role="list" className="flex flex-col divide-y divide-line px-5">
            {lines.map((line) => {
              const key = lineKey(line);
              return (
                <li key={key} className="py-4">
                  <CartItem
                    line={line}
                    onQuantityChange={(q) => setQuantity(key, q)}
                    onRemove={() => removeItem(key)}
                    onSaveForLater={() => saveForLater(key)}
                    onNavigate={close}
                  />
                </li>
              );
            })}
          </ul>
        </div>
      )}
    </Drawer>
  );
}
