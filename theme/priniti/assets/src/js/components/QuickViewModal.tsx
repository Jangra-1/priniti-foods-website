import { Check, ShoppingBag } from "lucide-react";
import { useState } from "react";
import { useCartStore } from "@theme/stores/cart";
import { useUIStore } from "@theme/stores/ui";
import type { QuickViewProduct } from "@theme/types";
import { ButtonLink, Button } from "./Button";
import { Modal } from "./Modal";
import { PriceDisplay } from "./PriceDisplay";
import { ProductImage } from "./ProductImage";
import { QuantitySelector } from "./QuantitySelector";

/**
 * Port of reference/nextjs/components/commerce/QuickViewModal.tsx with the compact ProductPurchasePanel
 * (pack size, price status, quantity, add to cart; no wishlist, no buy now).
 */
function PurchasePanel({ product }: { product: QuickViewProduct }) {
  const addItem = useCartStore((s) => s.addItem);
  const labelled = product.variants.filter((v) => v.label);
  const [variantId, setVariantId] = useState<number | undefined>((product.variants.find((v) => v.purchasable) ?? product.variants[0])?.id);
  const [quantity, setQuantity] = useState(1);
  const [added, setAdded] = useState(false);
  const variant = product.variants.find((v) => v.id === variantId);
  const buyable = variant?.purchasable && variant.price !== null ? variant : undefined;

  return (
    <div className="flex flex-col gap-5">
      {labelled.length > 0 ? (
        <fieldset>
          <legend className="mb-2 text-sm font-semibold">Pack size</legend>
          <div className="flex flex-wrap gap-2">
            {labelled.map((v) => (
              <label key={v.id} className="relative cursor-pointer">
                <input type="radio" name={`qv-pack-${product.id}`} value={v.id} checked={variantId === v.id} onChange={() => setVariantId(v.id)} className="peer sr-only" />
                <span className="flex min-h-11 min-w-20 items-center justify-center rounded-full border px-5 text-sm font-semibold transition-colors border-line bg-surface hover:border-ink peer-checked:border-navy peer-checked:bg-navy peer-checked:text-white peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand">
                  {v.label}
                </span>
              </label>
            ))}
          </div>
          {variant?.source === "image-filename" ? <p className="mt-2 text-xs text-ink-soft">Pack size to be confirmed.</p> : null}
        </fieldset>
      ) : (
        <p className="text-sm text-ink-soft">Pack size to be confirmed.</p>
      )}

      {buyable ? <PriceDisplay mrp={buyable.mrp ?? buyable.price!} price={buyable.price!} size="lg" /> : <p className="font-display text-xl font-bold text-ink-soft">Price coming soon</p>}

      {buyable ? (
        <div className="flex items-center gap-3">
          <span className="text-sm font-semibold">Quantity</span>
          <QuantitySelector value={quantity} onChange={setQuantity} label={`Quantity for ${product.name}`} />
        </div>
      ) : null}

      {buyable ? (
        <Button
          size="lg"
          fullWidth
          onClick={async () => {
            const ok = await addItem({ id: buyable.id, quantity, variation: buyable.attributes, name: product.name });
            if (ok) {
              setAdded(true);
              window.setTimeout(() => setAdded(false), 1600);
            }
          }}
        >
          {added ? <Check className="size-4" aria-hidden /> : <ShoppingBag className="size-4" aria-hidden />}
          {added ? "Added" : "Add to cart"}
        </Button>
      ) : (
        <>
          <Button size="lg" variant="secondary" fullWidth disabled>
            Price coming soon
          </Button>
          <p className="text-sm text-ink-soft">Online ordering for this product opens once its price is published.</p>
        </>
      )}
    </div>
  );
}

export function QuickViewModal() {
  const product = useUIStore((s) => s.quickViewProduct);
  const close = useUIStore((s) => s.closeQuickView);

  return (
    <Modal open={product !== null} onClose={close} title="Quick view">
      {product ? (
        <div className="grid gap-6 sm:grid-cols-[minmax(0,2fr)_3fr] sm:gap-8">
          <div className="relative mx-auto aspect-[4/5] w-full max-w-xs overflow-hidden rounded-card border border-line bg-surface sm:max-w-none">
            <ProductImage image={product.image ?? undefined} name={product.name} sizes="(min-width:640px) 260px, 70vw" />
          </div>
          <div className="flex flex-col gap-4">
            <div>
              <a href={product.categoryHref} onClick={close} className="text-sm font-medium text-ink-soft hover:text-brand">
                {product.categoryName}
              </a>
              <h3 className="mt-1 font-display text-2xl font-bold leading-tight">{product.name}</h3>
            </div>
            <PurchasePanel key={product.id} product={product} />
            <ButtonLink href={product.href} variant="secondary" onClick={close}>
              View full details
            </ButtonLink>
          </div>
        </div>
      ) : null}
    </Modal>
  );
}
