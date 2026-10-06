import { ShoppingBag } from "lucide-react";
import { config } from "@theme/config";
import { formatINR } from "@theme/lib/format";
import { saveForLater, useCartStore } from "@theme/stores/cart";
import { useUIStore } from "@theme/stores/ui";
import { ButtonLink } from "./Button";
import { CartItem } from "./CartItem";
import { Drawer } from "./Drawer";

/**
 * Port of reference/nextjs/components/commerce/CartDrawer.tsx, reading the WooCommerce cart (Store API).
 * The free-shipping bar stays hidden until a confirmed threshold is configured, as in the reference.
 */
export function CartDrawer() {
  const open = useUIStore((s) => s.cartOpen);
  const close = useUIStore((s) => s.closeCart);
  const status = useCartStore((s) => s.status);
  const lines = useCartStore((s) => s.lines);
  const itemCount = useCartStore((s) => s.itemCount);
  const subtotal = useCartStore((s) => s.subtotal);
  const { setQuantity, removeItem } = useCartStore.getState();

  const savings = Math.max(0, lines.reduce((n, l) => n + (l.mrp - l.price) * l.quantity, 0));
  const threshold = config.commerce.freeShippingThreshold;
  const freeShippingRemaining = threshold ? Math.max(0, threshold - subtotal) : undefined;
  const freeShippingProgress = threshold ? Math.min(1, subtotal / threshold) : undefined;

  const footer = lines.length ? (
    <div className="flex flex-col gap-3">
      <div className="flex items-baseline justify-between">
        <span className="text-sm text-ink-soft">Subtotal</span>
        <span className="font-display text-xl font-bold tabular-nums">{formatINR(subtotal)}</span>
      </div>
      {savings > 0 ? <p className="text-sm font-medium text-leaf">You save {formatINR(savings)} on MRP</p> : null}
      <p className="text-xs text-ink-soft">
        {config.commerce.pricesIncludeTax ? "Prices include tax. Shipping is calculated at checkout." : "Shipping and tax are not included in this subtotal."}
      </p>
      <div className="grid grid-cols-2 gap-2">
        <ButtonLink href={config.urls.cart} variant="secondary" onClick={close}>
          View cart
        </ButtonLink>
        <ButtonLink href={config.urls.checkout} onClick={close}>
          Checkout
        </ButtonLink>
      </div>
    </div>
  ) : null;

  return (
    <Drawer open={open} onClose={close} title={`Cart${itemCount ? ` (${itemCount})` : ""}`} footer={footer}>
      {status === "loading" && lines.length === 0 ? (
        <div aria-busy="true" className="m-5 h-40 animate-pulse rounded-card bg-line/50" />
      ) : lines.length === 0 ? (
        <div className="flex h-full flex-col items-center justify-center gap-4 px-8 py-16 text-center">
          <span className="flex size-16 items-center justify-center rounded-full bg-brand-tint text-brand">
            <ShoppingBag className="size-7" aria-hidden />
          </span>
          <div>
            <p className="font-display text-lg font-semibold">Your cart is empty</p>
            <p className="mt-1 text-sm text-ink-soft">Add a snack or two and they will show up here.</p>
          </div>
          <ButtonLink href={config.urls.shop} onClick={close}>
            Start shopping
          </ButtonLink>
        </div>
      ) : (
        <div className="flex flex-col">
          {freeShippingRemaining !== undefined && freeShippingProgress !== undefined ? (
            <div className="border-b border-line bg-canvas px-5 py-3">
              {freeShippingRemaining > 0 ? (
                <p className="text-sm">
                  Add <strong>{formatINR(freeShippingRemaining)}</strong> more for free shipping
                </p>
              ) : (
                <p className="text-sm font-medium text-leaf">You have unlocked free shipping</p>
              )}
              <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-line" role="presentation">
                <div className="h-full rounded-full bg-leaf transition-[width] duration-500" style={{ width: `${freeShippingProgress * 100}%` }} />
              </div>
            </div>
          ) : null}
          <ul role="list" className="flex flex-col divide-y divide-line px-5">
            {lines.map((line) => (
              <li key={line.key} className="py-4">
                <CartItem
                  line={line}
                  onQuantityChange={(q) => setQuantity(line.key, q)}
                  onRemove={() => removeItem(line.key)}
                  onSaveForLater={() => saveForLater(line)}
                  onNavigate={close}
                />
              </li>
            ))}
          </ul>
        </div>
      )}
    </Drawer>
  );
}
