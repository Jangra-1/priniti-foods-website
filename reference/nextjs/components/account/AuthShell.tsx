import type { ReactNode } from "react";
import { Container } from "@/components/layout/Container";
import { PackFan } from "@/components/sections/PackFan";
import type { Product } from "@/types/product";

interface AuthShellProps {
  title: string;
  subtitle: string;
  /** Headline and line on the branded visual panel. */
  panelTitle: string;
  panelText: string;
  /** Real packs from the catalog. */
  products: Product[];
  children: ReactNode;
}

/**
 * Branded two-column auth layout. Large screens: full navy panel with real packs on the left.
 * Phones: a slim strip of three packs above the form, so the form stays near the top.
 */
export function AuthShell({ title, subtitle, panelTitle, panelText, products, children }: AuthShellProps) {
  return (
    <section className="bg-canvas py-5 lg:py-10">
      <Container>
        <div className="mx-auto grid max-w-5xl overflow-hidden rounded-3xl bg-surface shadow-soft ring-1 ring-line/70 lg:grid-cols-[0.95fr_1.05fr]">
          {/* Phones and tablets: slim brand strip */}
          <div className="relative isolate h-32 overflow-hidden bg-navy sm:h-40 lg:hidden">
            <div aria-hidden className="absolute -right-8 -top-16 -z-10 size-44 rounded-full bg-navy-dark" />
            <div aria-hidden className="absolute -bottom-20 -left-6 -z-10 size-44 rounded-full bg-brand" />
            <PackFan products={products.slice(0, 3)} className="absolute inset-x-[22%] bottom-0 top-3 size-auto sm:inset-x-[30%]" />
            <p className="absolute left-3 top-3 rounded-lg bg-white px-2.5 py-1 font-display text-[11px] font-bold text-brand shadow-card">Swad Mein No.1</p>
          </div>

          {/* Large screens: full brand panel */}
          <div className="relative isolate hidden overflow-hidden bg-navy p-8 text-white lg:flex lg:flex-col lg:justify-between">
            <div aria-hidden className="absolute -right-16 -top-16 -z-10 size-60 rounded-full bg-navy-dark" />
            <div aria-hidden className="absolute -bottom-24 -left-12 -z-10 size-64 rounded-full bg-brand" />
            <div>
              <p className="inline-flex rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">Priniti Foods</p>
              <h2 className="mt-4 font-display text-3xl font-extrabold leading-tight">{panelTitle}</h2>
              <p className="mt-2 max-w-xs text-sm leading-relaxed text-white/80">{panelText}</p>
            </div>
            <div className="relative mt-8 aspect-[4/3] w-full">
              <PackFan products={products} className="absolute -inset-x-[3%] bottom-0 top-[6%] size-auto" />
            </div>
          </div>

          <div className="p-5 sm:p-8 lg:p-10">
            <h1 className="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">{title}</h1>
            <p className="mt-1 text-sm text-ink-soft sm:text-base">{subtitle}</p>
            <div className="mt-5">{children}</div>
          </div>
        </div>
      </Container>
    </section>
  );
}
