import { ArrowRight } from "lucide-react";
import Link from "next/link";
import { ProductImage } from "@/components/commerce/ProductImage";
import { Container } from "@/components/layout/Container";
import { cn } from "@/lib/cn";
import type { Product } from "@/types/product";

export interface PromoTile {
  eyebrow: string;
  title: string;
  href: string;
  cta: string;
  tone: "navy" | "leaf";
  /** Real products shown in the tile: their names form the description, the first pack is the picture. */
  products: Product[];
}

const tones = { navy: "bg-navy", leaf: "bg-leaf" } as const;

/** Two category tiles. Text only names real products; no offers or claims. */
export function PromoTiles({ tiles }: { tiles: PromoTile[] }) {
  const shown = tiles.filter((t) => t.products.length > 0);
  if (shown.length === 0) return null;
  return (
    <section aria-label="Shop sweets and bakery" className="bg-canvas py-4 lg:py-6">
      <Container>
        <ul role="list" className="grid gap-3 md:grid-cols-2 md:gap-4">
          {shown.map((t) => {
            const names = t.products.map((p) => p.name);
            return (
              <li key={t.href}>
                <div className={cn("relative isolate flex h-full min-h-44 overflow-hidden rounded-3xl p-5 text-white sm:p-6", tones[t.tone])}>
                  <div aria-hidden className="absolute -right-16 -top-20 -z-10 size-64 rounded-full bg-white/10" />
                  <div className="relative z-10 flex max-w-[62%] flex-col justify-between gap-4 sm:max-w-[58%]">
                    <div>
                      <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-white/75">{t.eyebrow}</p>
                      <h3 className="mt-1 font-display text-xl font-extrabold leading-tight sm:text-2xl">{t.title}</h3>
                      <p className="mt-1 text-xs leading-relaxed text-white/85 sm:text-sm">{names.join(", ")} and more.</p>
                    </div>
                    <Link
                      href={t.href}
                      className="inline-flex w-fit items-center gap-2 rounded-xl bg-white/20 px-4 py-2 text-xs font-semibold sm:text-sm backdrop-blur-sm transition-colors hover:bg-white hover:text-ink"
                    >
                      {t.cta}
                      <ArrowRight className="size-4" aria-hidden />
                    </Link>
                  </div>
                  <div className="absolute bottom-0 right-3 aspect-[3/4] w-[32%] max-w-32 translate-y-[6%] drop-shadow-xl sm:right-6">
                    <ProductImage image={t.products[0].images[0]} name={t.products[0].name} sizes="(min-width:768px) 20vw, 38vw" className="bg-transparent p-0" />
                  </div>
                </div>
              </li>
            );
          })}
        </ul>
      </Container>
    </section>
  );
}
