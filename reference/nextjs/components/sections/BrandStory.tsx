import { Container } from "@/components/layout/Container";
import { ButtonLink } from "@/components/ui/Button";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { homeContent } from "@/data/home";
import type { Product } from "@/types/product";
import { PackFan } from "./PackFan";

export function BrandStory({ products }: { products: Product[] }) {
  const { heading, paragraphs, cta } = homeContent.brandStory;
  return (
    <section aria-labelledby="story-heading" className="bg-canvas py-8 lg:py-12">
      <Container className="grid items-center gap-6 lg:grid-cols-2 lg:gap-12">
        <div className="max-w-lg">
          <Eyebrow className="mb-3">Our story</Eyebrow>
          <h2 id="story-heading" className="text-balance font-display text-2xl font-extrabold tracking-tight sm:text-3xl lg:text-4xl">
            {heading}
          </h2>
          <div className="mt-3 flex flex-col gap-3 text-base leading-relaxed text-ink-soft">
            {paragraphs.map((p) => (
              <p key={p}>{p}</p>
            ))}
          </div>
          <ButtonLink href={cta.href} variant="dark" size="md" className="mt-5 h-12 px-6">
            {cta.label}
          </ButtonLink>
        </div>
        <div className="relative isolate aspect-[16/11] overflow-hidden rounded-3xl bg-navy">
          <div aria-hidden className="absolute -left-[12%] -top-[15%] -z-10 size-[60%] rounded-full bg-navy-dark" />
          <div aria-hidden className="absolute -bottom-[25%] -right-[10%] -z-10 size-[58%] rounded-full bg-brand" />
          <PackFan products={products} className="px-[4%] pt-[10%]" />
          <p className="absolute right-4 top-4 rounded-xl bg-brand px-3 py-1.5 text-center font-display text-xs font-bold text-white shadow-lift">Swad Mein No.1</p>
        </div>
      </Container>
    </section>
  );
}
