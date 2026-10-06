import { cn } from "@/lib/cn";
import type { Product } from "@/types/product";
import { ProductCard } from "./ProductCard";

interface ProductGridProps {
  products: Product[];
  emptyTitle?: string;
  emptyText?: string;
  className?: string;
}

export function ProductGrid({ products, emptyTitle = "No products found", emptyText, className }: ProductGridProps) {
  if (products.length === 0) {
    return (
      <div className="rounded-card border border-dashed border-line px-6 py-14 text-center">
        <p className="font-display text-lg font-semibold">{emptyTitle}</p>
        {emptyText ? <p className="mt-1 text-ink-soft">{emptyText}</p> : null}
      </div>
    );
  }
  return (
    <ul role="list" className={cn("grid grid-cols-2 gap-3 lg:gap-4 md:grid-cols-3 xl:grid-cols-4", className)}>
      {products.map((p) => (
        <li key={p.id}>
          <ProductCard product={p} />
        </li>
      ))}
    </ul>
  );
}
