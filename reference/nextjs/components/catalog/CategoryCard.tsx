import { ArrowUpRight } from "lucide-react";
import Link from "next/link";
import { ProductImage } from "@/components/commerce/ProductImage";
import { tintForIndex } from "@/lib/category-style";
import type { Category } from "@/types/category";

interface CategoryCardProps {
  category: Category;
  index: number;
  productCount?: number;
}

export function CategoryCard({ category, index, productCount }: CategoryCardProps) {
  return (
    <Link
      href={`/category/${category.slug}`}
      className="group relative flex h-full flex-col overflow-hidden rounded-2xl bg-surface shadow-card ring-1 ring-line/70 transition duration-300 hover:-translate-y-1 hover:shadow-lift hover:ring-brand/50"
    >
      <div className={`relative aspect-square overflow-hidden ${tintForIndex(index)}`}>
        <ProductImage image={category.image} name={category.name} sizes="(min-width:1024px) 18vw, 40vw" className="bg-transparent p-2.5 text-ink/50" />
        <span
          aria-hidden
          className="absolute right-1.5 top-1.5 flex size-6 items-center justify-center rounded-full bg-surface text-ink shadow-card transition-colors group-hover:bg-brand group-hover:text-white"
        >
          <ArrowUpRight className="size-3.5" />
        </span>
      </div>
      <div className="flex flex-1 flex-col justify-center px-2.5 py-2">
        <h3 className="font-display text-xs font-semibold leading-tight sm:text-[13px]">{category.name}</h3>
        {productCount !== undefined ? (
          <p className="mt-0.5 text-[11px] text-ink-soft">
            {productCount === 0 ? "Coming soon" : `${productCount} ${productCount === 1 ? "product" : "products"}`}
          </p>
        ) : null}
      </div>
    </Link>
  );
}
