import type { ReactNode } from "react";
import type { Product } from "@/types/product";

function Section({ title, open, children }: { title: string; open?: boolean; children: ReactNode }) {
  return (
    <details open={open} className="group border-b border-line py-1">
      <summary className="flex cursor-pointer list-none items-center justify-between gap-4 py-4 font-display text-lg font-semibold [&::-webkit-details-marker]:hidden">
        {title}
        <span aria-hidden className="text-2xl font-normal leading-none transition-transform group-open:rotate-45">
          +
        </span>
      </summary>
      <div className="pb-5 text-ink-soft">{children}</div>
    </details>
  );
}

const pending = <p>Coming soon.</p>;

/** Shows supplied details only. Anything missing says "Coming soon", never filler content. */
export function ProductDetails({ product }: { product: Product }) {
  return (
    <div className="border-t border-line">
      <Section title="Description" open>
        {product.description ? <p className="max-w-2xl leading-relaxed">{product.description}</p> : pending}
      </Section>
      <Section title="Ingredients">{product.ingredients ? <p className="max-w-2xl leading-relaxed">{product.ingredients}</p> : pending}</Section>
      <Section title="Nutrition information">
        {product.nutrition?.length ? (
          <table className="w-full max-w-md text-left text-sm">
            <caption className="sr-only">Nutrition information per 100 g</caption>
            <thead>
              <tr className="border-b border-line text-ink">
                <th className="py-2 font-semibold">Nutrient</th>
                <th className="py-2 font-semibold">Per 100 g</th>
              </tr>
            </thead>
            <tbody>
              {product.nutrition.map((r) => (
                <tr key={r.label} className="border-b border-line">
                  <td className="py-2">{r.label}</td>
                  <td className="py-2 tabular-nums">{r.per100g}</td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          pending
        )}
      </Section>
      <Section title="Storage">{product.storage ? <p className="max-w-2xl leading-relaxed">{product.storage}</p> : pending}</Section>
      <Section title="Shipping and delivery">{product.shippingNote ? <p className="max-w-2xl leading-relaxed">{product.shippingNote}</p> : pending}</Section>
    </div>
  );
}
