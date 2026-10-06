import { ArrowRight, ShoppingCart, Sparkles } from "lucide-react";
import Link from "next/link";
import { Container } from "@/components/layout/Container";
import { ButtonLink } from "@/components/ui/Button";
import { homeContent } from "@/data/home";
import { siteConfig } from "@/data/site";
import { formatINR } from "@/lib/format";
import type { Category } from "@/types/category";
import type { Product } from "@/types/product";
import { PackFan } from "./PackFan";

interface HeroProps {
  products: Product[];
  categories: Category[];
}

/** Floating product tag: real product name and category from the catalog, no claims. */
function FloatingTag({ product, className }: { product: Product; className: string }) {
  return (
    <Link
      href={`/product/${product.slug}`}
      className={`absolute z-10 hidden max-w-[9.5rem] rounded-xl bg-surface px-3 py-2 shadow-lift transition-transform hover:-translate-y-0.5 sm:block ${className}`}
    >
      <span className="block text-[10px] font-bold uppercase tracking-wide text-brand">{product.categoryName}</span>
      <span className="mt-0.5 block font-display text-xs font-semibold leading-snug">{product.name}</span>
    </Link>
  );
}

export function Hero({ products, categories }: HeroProps) {
  const { hero } = homeContent;
  const [line1, ...rest] = hero.headline.split(/(?<=,)\s+/);
  const line2 = rest.join(" ");

  return (
    <section aria-labelledby="hero-heading" className="relative overflow-hidden bg-linear-to-br from-brand-tint via-blush to-surface">
      <Container className="grid items-center gap-6 py-8 sm:py-10 lg:grid-cols-[1.1fr_1fr] lg:gap-6 lg:py-10">
        <div className="max-w-xl">
          <p className="inline-flex items-center gap-2 rounded-full border border-line bg-surface px-3.5 py-1.5 text-[13px] font-semibold shadow-card">
            <Sparkles className="size-4 text-brand" aria-hidden />
            Swad Mein No.1
          </p>
          <h1 id="hero-heading" className="mt-4 font-display text-[2.25rem] font-extrabold leading-[1.05] tracking-tight sm:text-5xl lg:text-5xl xl:text-[3.25rem]">
            <span className="block">{line1}</span>
            {line2 ? <span className="block text-brand">{line2}</span> : null}
          </h1>
          <p className="mt-3 max-w-md text-base leading-relaxed text-ink-soft sm:text-lg">{hero.subcopy}</p>
          <div className="mt-6 flex flex-col gap-3 sm:flex-row">
            <ButtonLink href={hero.primaryCta.href} size="md" className="h-12 px-6 text-[15px]">
              <ShoppingCart className="size-5" aria-hidden />
              {hero.primaryCta.label}
              <ArrowRight className="size-4" aria-hidden />
            </ButtonLink>
            <ButtonLink href={hero.secondaryCta.href} variant="outline" size="md" className="h-12 px-6 text-[15px]">
              {hero.secondaryCta.label}
            </ButtonLink>
          </div>
          <ul role="list" className="mt-5 flex flex-wrap gap-2" aria-label="Browse categories">
            {categories.slice(0, 5).map((c) => (
              <li key={c.slug}>
                <Link
                  href={`/category/${c.slug}`}
                  className="inline-flex min-h-8 items-center rounded-full bg-surface px-3 text-xs font-semibold shadow-card ring-1 ring-line/70 transition-colors hover:text-brand hover:ring-brand/50"
                >
                  {c.name}
                </Link>
              </li>
            ))}
          </ul>
          {siteConfig.commerce.freeShippingThreshold ? (
            <p className="mt-5 text-sm font-medium text-ink-soft">Free shipping on orders above {formatINR(siteConfig.commerce.freeShippingThreshold)}</p>
          ) : null}
        </div>

        {/* Round stage with REAL packs (positioned only, never edited) */}
        <div className="relative mx-auto aspect-square w-full max-w-[26rem] xl:max-w-[29rem]">
          <div aria-hidden className="absolute inset-0 rounded-full border-2 border-dashed border-brand/25" />
          <div aria-hidden className="absolute inset-[5%] overflow-hidden rounded-full bg-brand">
            <div className="absolute -right-[12%] -top-[12%] size-[62%] rounded-full bg-navy" />
            <div className="absolute -bottom-[18%] -left-[12%] size-[52%] rounded-full bg-brand-dark" />
          </div>
          <PackFan products={products} priority className="absolute -inset-x-[3%] bottom-[18%] top-[10%] size-auto" />
          {products[0] ? <FloatingTag product={products[0]} className="-left-1 top-[12%] lg:-left-6" /> : null}
          {products[3] ? <FloatingTag product={products[3]} className="-right-1 bottom-[12%] lg:-right-6" /> : null}
        </div>
      </Container>
    </section>
  );
}
