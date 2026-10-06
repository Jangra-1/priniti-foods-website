import { Container } from "@/components/layout/Container";
import { ButtonLink } from "@/components/ui/Button";
import { MediaImage } from "@/components/ui/MediaImage";
import { home } from "@/data/home";

export function PromoBanner() {
  const { promo } = home;
  return (
    <section aria-labelledby="promo-heading" className="py-6 lg:py-10">
      <Container>
        <div className="grid items-center overflow-hidden rounded-[2rem] bg-brand text-white lg:grid-cols-[1.1fr_0.9fr]">
          <div className="p-8 sm:p-12 lg:p-16">
            <h2 id="promo-heading" className="font-display text-4xl font-extrabold leading-[1.08] sm:text-5xl lg:text-6xl">
              {promo.headline}
            </h2>
            <p className="mt-4 max-w-sm text-lg text-white/85">{promo.copy}</p>
            <ButtonLink href={promo.cta.href} variant="light" size="lg" className="mt-8 w-full sm:w-auto">
              {promo.cta.label}
            </ButtonLink>
          </div>
          <div className="relative h-60 overflow-hidden sm:h-72 lg:h-full lg:min-h-[22rem]">
            <div aria-hidden className="absolute -bottom-1/3 left-1/2 size-[110%] -translate-x-1/2 rounded-full bg-navy lg:-bottom-1/4 lg:left-1/3 lg:size-[85%]" />
            <MediaImage
              image={promo.image}
              placeholderLabel="Combo pack image pending"
              sizes="(min-width:1024px) 30vw, 60vw"
              imageClassName="object-contain p-2"
              className="absolute left-1/2 top-1/2 aspect-[4/3] w-[62%] -translate-x-1/2 -translate-y-1/2 rotate-3 rounded-2xl bg-surface shadow-lift lg:w-[58%]"
            />
          </div>
        </div>
      </Container>
    </section>
  );
}
