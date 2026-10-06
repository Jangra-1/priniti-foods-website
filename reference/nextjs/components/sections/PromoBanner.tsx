import Link from "next/link";
import { Container } from "@/components/layout/Container";
import { ButtonLink } from "@/components/ui/Button";
import { formatINR } from "@/lib/format";
import { getDefaultVariant } from "@/lib/product";
import type { Product } from "@/types/product";
import { PackFan } from "./PackFan";

interface PromoBannerProps {
  headline: string;
  description: string;
  cta: { label: string; href: string };
  /** Small pill above the headline, e.g. "Coming soon". */
  badge?: string;
  /** Combo products, if any exist (none do yet). */
  products?: Product[];
  /** Real packs shown in the round visual on the right. */
  visualProducts?: Product[];
}

export function PromoBanner({ headline, description, cta, badge, products = [], visualProducts = [] }: PromoBannerProps) {
  const [first, ...others] = headline.split(/(?<=\.)\s+/);
  return (
    <section aria-labelledby="promo-heading" className="bg-canvas py-4 lg:py-6">
      <Container>
        <div className="relative isolate grid items-center gap-5 overflow-hidden rounded-3xl bg-brand px-6 py-8 text-white sm:px-8 lg:grid-cols-[1.1fr_1fr] lg:px-12 lg:py-8">
          <div aria-hidden className="absolute -right-24 -top-28 -z-10 size-80 rounded-full bg-white/10 sm:size-[26rem]" />
          <div aria-hidden className="absolute -bottom-32 -left-20 -z-10 size-72 rounded-full bg-brand-dark/60" />
          <div className="max-w-xl">
            {badge ? (
              <span className="mb-3 inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{badge}</span>
            ) : null}
            <h2 id="promo-heading" className="text-balance font-display text-3xl font-extrabold leading-[1.08] tracking-tight sm:text-4xl lg:text-5xl">
              <span className="block">{first}</span>
              {others.length ? <span className="block text-white/80">{others.join(" ")}</span> : null}
            </h2>
            <p className="mt-3 max-w-md text-base text-white/90">{description}</p>
            {products.length > 0 ? (
              <ul role="list" className="mt-6 flex flex-wrap gap-2">
                {products.map((p) => {
                  const v = getDefaultVariant(p);
                  return (
                    <li key={p.id}>
                      <Link href={`/product/${p.slug}`} className="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-medium transition-colors hover:bg-white/25">
                        {p.name}
                        {v?.price !== undefined ? <span className="font-semibold">{formatINR(v.price)}</span> : null}
                      </Link>
                    </li>
                  );
                })}
              </ul>
            ) : null}
            <ButtonLink href={cta.href} variant="light" size="md" className="mt-5 h-12 px-6">
              {cta.label}
            </ButtonLink>
          </div>
          {visualProducts.length > 0 ? (
            <div className="relative mx-auto aspect-[16/9] w-full max-w-sm lg:max-w-none">
              <div aria-hidden className="absolute inset-x-[10%] inset-y-0 rounded-full bg-navy" />
              <PackFan products={visualProducts} className="absolute inset-x-[2%] bottom-[10%] top-[16%] size-auto" />
            </div>
          ) : null}
        </div>
      </Container>
    </section>
  );
}
