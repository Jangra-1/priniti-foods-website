import { ArrowRight } from "lucide-react";
import Link from "next/link";
import { Badge } from "@/components/ui/Badge";
import { cn } from "@/lib/cn";
import { badgeLabels, getDefaultVariant, getDiscount, getPurchasableVariant } from "@/lib/product";
import type { Product } from "@/types/product";
import { AddToCartButton } from "./AddToCartButton";
import { PriceDisplay } from "./PriceDisplay";
import { ProductImage } from "./ProductImage";
import { QuickViewButton } from "./QuickViewButton";
import { RatingStars } from "./RatingStars";
import { WishlistButton } from "./WishlistButton";

/**
 * Server component (visual layer only changed: behaviour, data and variant logic are untouched).
 * Interactivity lives in the WishlistButton, QuickViewButton and AddToCartButton islands.
 */
export function ProductCard({ product, className }: { product: Product; className?: string }) {
  const variant = getDefaultVariant(product);
  const purchasable = getPurchasableVariant(product);
  const off = getDiscount(purchasable);
  const packs = product.variants.filter((v) => v.source !== "test-placeholder").map((v) => v.label);

  return (
    <article
      className={cn(
        "group relative flex h-full flex-col overflow-hidden rounded-2xl bg-surface shadow-card ring-1 ring-line/70 transition duration-300 hover:-translate-y-0.5 hover:shadow-lift has-[.card-link:focus-visible]:ring-2 has-[.card-link:focus-visible]:ring-brand",
        className,
      )}
    >
      <div className="relative aspect-[5/4] overflow-hidden bg-surface">
        <ProductImage image={product.images[0]} name={product.name} sizes="(min-width:1280px) 22vw, (min-width:768px) 30vw, 46vw" />
        <div className="absolute left-2 top-2 flex flex-col items-start gap-1">
          {product.badges?.map((b) => (
            <Badge key={b} tone={b === "new" ? "leaf" : "navy"} className="px-2 py-0.5 text-[10px] shadow-card">
              {badgeLabels[b]}
            </Badge>
          ))}
          {off > 0 ? <Badge tone="leaf" className="px-2 py-0.5 text-[10px]">{off}% off</Badge> : null}
        </div>
        <WishlistButton productId={product.id} productName={product.name} className="absolute right-2 top-2 z-10 size-8" />
        <QuickViewButton product={product} />
      </div>

      <div className="flex flex-1 flex-col gap-0.5 p-3">
        <Link
          href={`/category/${product.categorySlug}`}
          className="relative z-10 w-fit text-[10px] font-bold uppercase tracking-wide text-brand transition-colors hover:text-brand-dark"
        >
          {product.categoryName}
        </Link>
        {variant ? (
          <p className="text-[11px] leading-tight text-ink-soft">{packs.length ? packs.join(" · ") : "Pack size to be confirmed"}</p>
        ) : null}

        <h3 className="font-display text-sm font-semibold leading-snug">
          <Link href={`/product/${product.slug}`} className="card-link outline-none after:absolute after:inset-0">
            {product.name}
          </Link>
        </h3>

        {product.reviewCount && product.rating !== undefined ? <RatingStars rating={product.rating} reviewCount={product.reviewCount} className="mt-0.5" /> : null}

        <div className="relative z-10 mt-auto flex items-end justify-between gap-2 pt-2">
          {purchasable ? (
            <>
              <PriceDisplay mrp={purchasable.mrp ?? purchasable.price} price={purchasable.price} size="sm" showDiscount={false} isTest={purchasable.isTestPrice} />
              <AddToCartButton product={product} iconOnly />
            </>
          ) : (
            <>
              <p className="text-xs font-semibold text-ink-soft">Price coming soon</p>
              <Link
                href={`/product/${product.slug}`}
                aria-label={`View details: ${product.name}`}
                className="inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-tint text-brand transition duration-200 hover:bg-brand hover:text-white"
              >
                <ArrowRight className="size-4" aria-hidden />
              </Link>
            </>
          )}
        </div>
      </div>
    </article>
  );
}
