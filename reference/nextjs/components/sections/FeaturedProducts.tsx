"use client";

import { ArrowRight } from "lucide-react";
import Link from "next/link";
import { useMemo, useState, type ReactNode } from "react";
import { Container } from "@/components/layout/Container";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { cn } from "@/lib/cn";

export interface FeaturedItem {
  key: string;
  category: { slug: string; name: string };
  /** A server-rendered <ProductCard/>. This component only decides which ones are visible. */
  node: ReactNode;
}

interface FeaturedProductsProps {
  eyebrow: string;
  title: string;
  href: string;
  items: FeaturedItem[];
}

/** Category tabs over the existing ProductCard grid. Filtering is purely visual (client-side). */
export function FeaturedProducts({ eyebrow, title, href, items }: FeaturedProductsProps) {
  const tabs = useMemo(() => {
    const seen = new Map<string, string>();
    items.forEach((i) => seen.set(i.category.slug, i.category.name));
    return [...seen].slice(0, 4).map(([slug, name]) => ({ slug, name }));
  }, [items]);
  const [active, setActive] = useState("all");
  const shown = active === "all" ? items : items.filter((i) => i.category.slug === active);

  const pill = (on: boolean) =>
    cn(
      "inline-flex min-h-8 items-center rounded-full px-3.5 text-[13px] font-semibold transition-colors",
      on ? "bg-brand text-white" : "bg-surface text-ink ring-1 ring-line hover:text-brand hover:ring-brand/50",
    );

  return (
    <section aria-labelledby="featured-products-heading" className="bg-canvas py-8 lg:py-10">
      <Container>
        <div className="mb-5 flex flex-col gap-3 lg:mb-6 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <Eyebrow className="mb-2">{eyebrow}</Eyebrow>
            <h2 id="featured-products-heading" className="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
              {title}
            </h2>
          </div>
          <div className="flex flex-wrap items-center gap-2">
            <div role="group" aria-label="Filter featured products by category" className="flex flex-wrap gap-2">
              <button type="button" aria-pressed={active === "all"} onClick={() => setActive("all")} className={pill(active === "all")}>
                All
              </button>
              {tabs.map((t) => (
                <button key={t.slug} type="button" aria-pressed={active === t.slug} onClick={() => setActive(t.slug)} className={pill(active === t.slug)}>
                  {t.name}
                </button>
              ))}
            </div>
            <Link href={href} className="ml-1 inline-flex items-center gap-1.5 text-sm font-semibold text-brand hover:text-brand-dark">
              View All
              <ArrowRight className="size-4" aria-hidden />
            </Link>
          </div>
        </div>
        <ul role="list" aria-live="polite" className="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4 lg:gap-4">
          {shown.map((i) => (
            <li key={i.key}>{i.node}</li>
          ))}
        </ul>
      </Container>
    </section>
  );
}
