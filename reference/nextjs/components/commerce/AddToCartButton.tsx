"use client";

import { Check, ShoppingBag, ShoppingCart } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import { Button, type ButtonSize, type ButtonVariant } from "@/components/ui/Button";
import { getPurchasableVariant, isPurchasable } from "@/lib/product";
import { toast } from "@/store/toast";
import { useCartStore } from "@/store/cart";
import type { Product } from "@/types/product";

interface AddToCartButtonProps {
  product: Product;
  variantId?: string;
  quantity?: number;
  size?: ButtonSize;
  variant?: ButtonVariant;
  fullWidth?: boolean;
  label?: string;
  className?: string;
  /** Round icon-only button (product cards). Same behaviour, same accessible name. */
  iconOnly?: boolean;
  /** Called after an item was actually added (e.g. open the cart drawer). */
  onAdded?: () => void;
}

export function AddToCartButton({
  product,
  variantId,
  quantity = 1,
  size = "md",
  variant = "primary",
  fullWidth = true,
  label = "Add to cart",
  className,
  iconOnly = false,
  onAdded,
}: AddToCartButtonProps) {
  const addItem = useCartStore((s) => s.addItem);
  const [added, setAdded] = useState(false);
  const timer = useRef<ReturnType<typeof setTimeout>>(undefined);
  useEffect(() => () => clearTimeout(timer.current), []);

  // An explicitly chosen variant is never swapped for another one: no price means no add.
  const picked = variantId ? product.variants.find((v) => v.id === variantId) : undefined;
  const chosen = variantId ? (picked && isPurchasable(picked) ? picked : undefined) : getPurchasableVariant(product);

  const iconStyles =
    "inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-tint text-brand transition duration-200 hover:bg-brand hover:text-white active:scale-95 disabled:pointer-events-none disabled:opacity-50";

  if (!chosen && iconOnly) {
    return (
      <button type="button" disabled aria-label={`${product.name}: price coming soon`} className={`${iconStyles} ${className ?? ""}`}>
        <ShoppingCart className="size-4" aria-hidden />
      </button>
    );
  }

  if (!chosen) {
    return (
      <Button size={size} variant="secondary" fullWidth={fullWidth} disabled className={className}>
        Price coming soon
      </Button>
    );
  }

  const handleAdd = () => {
    addItem(
      {
        productId: product.id,
        slug: product.slug,
        name: product.name,
        variantId: chosen.id,
        variantLabel: chosen.label,
        image: product.images[0],
        price: chosen.price,
        mrp: chosen.mrp ?? chosen.price,
        isTestPrice: chosen.isTestPrice,
      },
      quantity,
    );
    toast({ title: "Added to cart", description: product.name });
    setAdded(true);
    onAdded?.();
    clearTimeout(timer.current);
    timer.current = setTimeout(() => setAdded(false), 1600);
  };

  if (iconOnly) {
    return (
      <button type="button" onClick={handleAdd} aria-label={added ? `${product.name} added to cart` : `Add ${product.name} to cart`} className={`${iconStyles} ${className ?? ""}`}>
        {added ? <Check className="size-4" aria-hidden /> : <ShoppingCart className="size-4" aria-hidden />}
      </button>
    );
  }

  return (
    <Button size={size} variant={variant} fullWidth={fullWidth} className={className} onClick={handleAdd}>
      {added ? <Check className="size-4" aria-hidden /> : <ShoppingBag className="size-4" aria-hidden />}
      {added ? "Added" : label}
    </Button>
  );
}
