"use client";

import Image from "next/image";
import { useState } from "react";
import { cn } from "@/lib/cn";
import type { ImageAsset } from "@/types/product";
import { ProductImage } from "./ProductImage";

export function ProductGallery({ images, name }: { images: ImageAsset[]; name: string }) {
  const [index, setIndex] = useState(0);
  const current = images[index];

  return (
    <div className="flex flex-col gap-3">
      <div className="relative aspect-square overflow-hidden rounded-card border border-line bg-surface lg:aspect-[4/5]">
        <ProductImage image={current} name={name} sizes="(min-width:1024px) 50vw, 94vw" priority className="p-6 sm:p-10" />
      </div>
      {images.length > 1 ? (
        <ul role="list" className="flex gap-2" aria-label={`${name} images`}>
          {images.map((img, i) => (
            <li key={img.src}>
              <button
                type="button"
                onClick={() => setIndex(i)}
                aria-label={`Show image ${i + 1} of ${images.length}`}
                aria-current={i === index}
                className={cn(
                  "relative block size-20 overflow-hidden rounded-xl border bg-surface transition-colors",
                  i === index ? "border-navy" : "border-line hover:border-ink",
                )}
              >
                <Image src={img.src} alt="" fill sizes="80px" className="object-contain p-1.5" />
              </button>
            </li>
          ))}
        </ul>
      ) : null}
    </div>
  );
}
