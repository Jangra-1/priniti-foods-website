"use client";

import { Eye } from "lucide-react";
import { useUIStore } from "@/store/ui";
import type { Product } from "@/types/product";

/** Opens the global Quick View modal. Always visible on touch, revealed on hover/focus on desktop. */
export function QuickViewButton({ product }: { product: Product }) {
  const open = useUIStore((s) => s.openQuickView);
  return (
    <button
      type="button"
      onClick={() => open(product)}
      aria-label={`Quick view: ${product.name}`}
      className="absolute inset-x-2 bottom-2 z-10 inline-flex h-8 items-center justify-center gap-1.5 rounded-full bg-surface/95 text-xs font-semibold shadow-card transition duration-200 hover:bg-ink hover:text-white lg:translate-y-1 lg:opacity-0 lg:group-focus-within:translate-y-0 lg:group-focus-within:opacity-100 lg:group-hover:translate-y-0 lg:group-hover:opacity-100"
    >
      <Eye className="size-3.5" aria-hidden />
      Quick view
    </button>
  );
}
