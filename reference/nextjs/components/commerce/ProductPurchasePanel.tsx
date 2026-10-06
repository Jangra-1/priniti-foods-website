"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { Button } from "@/components/ui/Button";
import { getPurchasableVariant, isPurchasable } from "@/lib/product";
import { useCartStore } from "@/store/cart";
import { useUIStore } from "@/store/ui";
import type { Product } from "@/types/product";
import { AddToCartButton } from "./AddToCartButton";
import { PackSizeSelector } from "./PackSizeSelector";
import { PriceDisplay } from "./PriceDisplay";
import { QuantitySelector } from "./QuantitySelector";
import { WishlistButton } from "./WishlistButton";

interface ProductPurchasePanelProps {
  product: Product;
  /** Quick View: no wishlist, no sticky mobile bar, no buy-now. */
  compact?: boolean;
  onNavigate?: () => void;
}

/**
 * Pack size, price status, quantity and cart actions for one product.
 * A product can only be bought when the selected pack size has a real price.
 */
export function ProductPurchasePanel({ product, compact = false }: ProductPurchasePanelProps) {
  const router = useRouter();
  const openCart = useUIStore((s) => s.openCart);
  const addItem = useCartStore((s) => s.addItem);
  const [variantId, setVariantId] = useState<string | undefined>((getPurchasableVariant(product) ?? product.variants[0])?.id);
  const [quantity, setQuantity] = useState(1);

  const variant = product.variants.find((v) => v.id === variantId);
  const buyable = variant && isPurchasable(variant) ? variant : undefined;

  const priceBlock = buyable ? (
    <PriceDisplay mrp={buyable.mrp ?? buyable.price} price={buyable.price} size="lg" isTest={buyable.isTestPrice} />
  ) : (
    <p className="font-display text-xl font-bold text-ink-soft">Price coming soon</p>
  );

  return (
    <div className="flex flex-col gap-5">
      {product.variants.length > 0 ? (
        <PackSizeSelector variants={product.variants} value={variantId} onChange={setVariantId} />
      ) : (
        <p className="text-sm text-ink-soft">Pack size to be confirmed.</p>
      )}

      {priceBlock}

      {buyable ? (
        <div className="flex items-center gap-3">
          <span className="text-sm font-semibold">Quantity</span>
          <QuantitySelector value={quantity} onChange={setQuantity} label={`Quantity for ${product.name}`} />
        </div>
      ) : null}

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <AddToCartButton
          product={product}
          variantId={variantId}
          quantity={quantity}
          size="lg"
          fullWidth
          className="sm:flex-1"
          onAdded={compact ? undefined : openCart}
        />
        {!compact && buyable ? (
          <Button
            variant="dark"
            size="lg"
            className="sm:flex-1"
            onClick={() => {
              addItem(
                {
                  productId: product.id,
                  slug: product.slug,
                  name: product.name,
                  variantId: buyable.id,
                  variantLabel: buyable.label,
                  image: product.images[0],
                  price: buyable.price,
                  mrp: buyable.mrp ?? buyable.price,
                  isTestPrice: buyable.isTestPrice,
                },
                quantity,
              );
              router.push("/checkout");
            }}
          >
            Buy now
          </Button>
        ) : null}
        {!compact ? <WishlistButton productId={product.id} productName={product.name} className="size-12 self-start border border-line sm:self-auto" /> : null}
      </div>

      {!buyable ? (
        <p className="text-sm text-ink-soft">Online ordering for this product opens once its price is published.</p>
      ) : null}
    </div>
  );
}
