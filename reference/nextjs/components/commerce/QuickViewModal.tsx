"use client";

import Link from "next/link";
import { Modal } from "@/components/ui/Modal";
import { ButtonLink } from "@/components/ui/Button";
import { useUIStore } from "@/store/ui";
import { ProductImage } from "./ProductImage";
import { ProductPurchasePanel } from "./ProductPurchasePanel";

/** Mounted once in the root layout. Shows only data already on the product; never invents any. */
export function QuickViewModal() {
  const product = useUIStore((s) => s.quickViewProduct);
  const close = useUIStore((s) => s.closeQuickView);

  return (
    <Modal open={product !== null} onClose={close} title="Quick view">
      {product ? (
        <div className="grid gap-6 sm:grid-cols-[minmax(0,2fr)_3fr] sm:gap-8">
          <div className="relative mx-auto aspect-[4/5] w-full max-w-xs overflow-hidden rounded-card border border-line bg-surface sm:max-w-none">
            <ProductImage image={product.images[0]} name={product.name} sizes="(min-width:640px) 260px, 70vw" />
          </div>
          <div className="flex flex-col gap-4">
            <div>
              <Link href={`/category/${product.categorySlug}`} onClick={close} className="text-sm font-medium text-ink-soft hover:text-brand">
                {product.categoryName}
              </Link>
              <h3 className="mt-1 font-display text-2xl font-bold leading-tight">{product.name}</h3>
            </div>
            <ProductPurchasePanel key={product.id} product={product} compact />
            <ButtonLink href={`/product/${product.slug}`} variant="secondary" onClick={close}>
              View full details
            </ButtonLink>
          </div>
        </div>
      ) : null}
    </Modal>
  );
}
