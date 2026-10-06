import { Package } from "lucide-react";
import { cn } from "@theme/lib/cn";

/** Port of reference/nextjs/components/commerce/ProductImage.tsx. Fills its (relatively positioned) parent like next/image `fill`. */
interface ProductImageProps {
  image?: { src: string; alt: string };
  name: string;
  sizes: string;
  className?: string;
}

export function ProductImage({ image, name, sizes, className }: ProductImageProps) {
  if (!image) {
    return (
      <div
        role="img"
        aria-label={`${name}: product image coming soon`}
        className={cn("flex size-full flex-col items-center justify-center gap-1.5 bg-brand-tint p-2 text-center text-brand", className)}
      >
        <Package className="size-8" strokeWidth={1.5} aria-hidden />
        <span className="text-xs font-medium leading-tight text-ink-soft">Image pending</span>
      </div>
    );
  }
  return (
    <img
      src={image.src}
      alt={image.alt}
      sizes={sizes}
      loading="lazy"
      decoding="async"
      className={cn("absolute inset-0 size-full object-contain p-4 transition-transform duration-500 ease-out group-hover:scale-105", className)}
    />
  );
}
