import Image from "next/image";
import { Package } from "lucide-react";
import { cn } from "@/lib/cn";
import type { ImageAsset } from "@/types/product";

interface ProductImageProps {
  image?: ImageAsset;
  name: string;
  sizes: string;
  priority?: boolean;
  className?: string;
}

/** Fills its (relatively positioned) parent. Shows a neutral placeholder until real packshots exist. */
export function ProductImage({ image, name, sizes, priority, className }: ProductImageProps) {
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
    <Image
      src={image.src}
      alt={image.alt}
      fill
      sizes={sizes}
      priority={priority}
      className={cn("object-contain p-4 transition-transform duration-500 ease-out group-hover:scale-105", className)}
    />
  );
}
