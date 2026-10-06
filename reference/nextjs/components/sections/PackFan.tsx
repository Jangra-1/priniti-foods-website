import Image from "next/image";
import { cn } from "@/lib/cn";
import type { Product } from "@/types/product";

/** left/width are % of the stage; rotation in degrees. Centre pack is largest and on top. */
const LAYOUTS: Record<number, { left: number; width: number; rotate: number; z: number; hideOnMobile?: boolean }[]> = {
  3: [
    { left: 4, width: 30, rotate: -8, z: 1 },
    { left: 33, width: 36, rotate: 0, z: 3 },
    { left: 66, width: 30, rotate: 8, z: 1 },
  ],
  5: [
    { left: 0, width: 24, rotate: -12, z: 1, hideOnMobile: true },
    { left: 13, width: 27, rotate: -6, z: 2 },
    { left: 33, width: 34, rotate: 0, z: 4 },
    { left: 60, width: 27, rotate: 6, z: 2 },
    { left: 76, width: 24, rotate: 12, z: 1, hideOnMobile: true },
  ],
};

/**
 * A composition of REAL pack images. Packaging is shown unaltered (only positioned and slightly rotated).
 * Packs are Images with empty alt because they are decorative here; names are in the surrounding content.
 */
export function PackFan({ products, className, priority = false }: { products: Product[]; className?: string; priority?: boolean }) {
  const items = products.filter((p) => p.images[0]);
  const layout = LAYOUTS[items.length === 5 ? 5 : 3];
  const shown = items.slice(0, layout.length);
  const centre = Math.floor(shown.length / 2);

  return (
    <div className={cn("relative size-full", className)}>
      {shown.map((p, i) => {
        const pos = layout[i];
        return (
          <div
            key={p.slug}
            className={cn("absolute bottom-[8%] aspect-[3/4] drop-shadow-xl", pos.hideOnMobile && "hidden sm:block")}
            style={{ left: `${pos.left}%`, width: `${pos.width}%`, zIndex: pos.z, transform: `rotate(${pos.rotate}deg)` }}
          >
            <Image
              src={p.images[0].src}
              alt=""
              fill
              sizes="(min-width:1024px) 16vw, (min-width:640px) 22vw, 30vw"
              priority={priority && i === centre}
              className="object-contain"
            />
          </div>
        );
      })}
    </div>
  );
}
