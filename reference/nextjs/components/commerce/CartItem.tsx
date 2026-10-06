"use client";

import Link from "next/link";
import { Bookmark, Trash2 } from "lucide-react";
import { formatINR } from "@/lib/format";
import type { CartLine } from "@/types/cart";
import { PriceDisplay } from "./PriceDisplay";
import { ProductImage } from "./ProductImage";
import { QuantitySelector } from "./QuantitySelector";

interface CartItemProps {
  line: CartLine;
  onQuantityChange: (quantity: number) => void;
  onRemove: () => void;
  onSaveForLater?: () => void;
  onNavigate?: () => void;
}

export function CartItem({ line, onQuantityChange, onRemove, onSaveForLater, onNavigate }: CartItemProps) {
  return (
    <div className="flex gap-3.5">
      <div className="relative size-20 shrink-0 overflow-hidden rounded-xl border border-line bg-surface sm:size-24">
        <ProductImage image={line.image} name={line.name} sizes="96px" />
      </div>
      <div className="flex min-w-0 flex-1 flex-col gap-1.5">
        <div className="flex items-start justify-between gap-3">
          <div className="min-w-0">
            <Link href={`/product/${line.slug}`} onClick={onNavigate} className="line-clamp-2 font-display text-sm font-semibold leading-snug hover:text-brand">
              {line.name}
            </Link>
            <p className="mt-0.5 text-xs text-ink-soft">{line.variantLabel}</p>
            <p className="text-xs text-ink-soft">{formatINR(line.price)} each</p>
          </div>
          <PriceDisplay mrp={line.mrp * line.quantity} price={line.price * line.quantity} size="sm" showDiscount={false} isTest={line.isTestPrice} className="shrink-0 flex-col items-end gap-0" />
        </div>
        <div className="mt-auto flex flex-wrap items-center justify-between gap-2">
          <QuantitySelector size="sm" value={line.quantity} onChange={onQuantityChange} label={`Quantity for ${line.name}`} />
          <div className="flex items-center gap-3 text-xs font-medium text-ink-soft">
            {onSaveForLater ? (
              <button type="button" onClick={onSaveForLater} className="inline-flex items-center gap-1 hover:text-ink">
                <Bookmark className="size-3.5" aria-hidden /> Save for later
              </button>
            ) : null}
            <button type="button" onClick={onRemove} aria-label={`Remove ${line.name} from cart`} className="inline-flex items-center gap-1 hover:text-brand">
              <Trash2 className="size-3.5" aria-hidden /> Remove
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
