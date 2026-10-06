import Image from "next/image";
import { tintForIndex } from "@/lib/category-style";
import type { Category } from "@/types/category";

interface CategoryBannerProps {
  category: Category;
  index: number;
  productCount: number;
}

export function CategoryBanner({ category, index, productCount }: CategoryBannerProps) {
  return (
    <div className={`relative overflow-hidden rounded-[2rem] ${tintForIndex(index)}`}>
      <div className="relative z-10 max-w-2xl px-6 py-6 sm:px-8 sm:py-8 lg:px-10 lg:py-10">
        <h1 className="font-display text-3xl font-extrabold sm:text-4xl">{category.name}</h1>
        {category.description ? <p className="mt-2 max-w-lg text-base text-ink-soft">{category.description}</p> : null}
        <p className="mt-5 inline-flex rounded-full bg-surface px-3.5 py-1.5 text-sm font-semibold">
          {productCount === 0 ? "Coming soon" : `${productCount} ${productCount === 1 ? "product" : "products"}`}
        </p>
      </div>
      {category.image ? (
        <Image src={category.image.src} alt={category.image.alt} width={480} height={480} className="absolute -right-6 bottom-0 hidden h-full w-auto object-contain md:block" />
      ) : (
        <div aria-hidden className="absolute -right-16 -top-16 hidden size-72 rounded-full bg-surface/60 md:block" />
      )}
    </div>
  );
}
