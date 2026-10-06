import Link from "next/link";
import { ButtonLink } from "@/components/ui/Button";
import { MediaImage } from "@/components/ui/MediaImage";
import { Container } from "@/components/layout/Container";
import { home } from "@/data/home";
import { siteConfig } from "@/data/site";
import { cn } from "@/lib/cn";
import { formatINR } from "@/lib/format";
import type { ImageAsset } from "@/types/product";

const slots = [
  "left-[5%] top-[34%] w-[33%] -rotate-6",
  "left-1/2 top-[14%] w-[44%] -translate-x-1/2 z-10",
  "right-[5%] top-[38%] w-[33%] rotate-6",
];

function HeroVisual({ images }: { images: (ImageAsset | undefined)[] }) {
  return (
    <div
      className="relative mx-auto aspect-[6/5] w-full max-w-xl animate-rise overflow-hidden rounded-[2rem] bg-brand lg:aspect-[4/5] lg:max-w-none"
      style={{ animationDelay: "140ms" }}
    >
      <div aria-hidden className="absolute left-1/2 top-[48%] size-[74%] -translate-x-1/2 -translate-y-1/2 rounded-full bg-navy" />
      {slots.map((pos, i) => (
        <MediaImage
          key={i}
          image={images[i]}
          placeholderLabel="Packshot pending"
          sizes="(min-width:1024px) 20vw, 30vw"
          priority={i === 1}
          imageClassName="object-contain p-2"
          className={cn("absolute aspect-[3/4] rounded-2xl bg-surface shadow-lift", pos)}
        />
      ))}
      {siteConfig.commerce.freeShippingThreshold ? (
        <Link
          href="/shop"
          className="absolute bottom-4 left-4 rounded-full bg-surface px-4 py-2 text-sm font-semibold shadow-card transition-transform hover:-translate-y-0.5"
        >
          Free shipping above {formatINR(siteConfig.commerce.freeShippingThreshold)}
        </Link>
      ) : null}
    </div>
  );
}

export function Hero() {
  const { hero } = home;
  return (
    <section aria-labelledby="hero-heading">
      <Container className="grid items-center gap-8 pb-10 pt-6 sm:pt-10 lg:grid-cols-[1.05fr_0.95fr] lg:gap-14 lg:pb-20 lg:pt-12">
        <div className="animate-rise">
          <h1 id="hero-heading" className="font-display text-[2.6rem] font-extrabold leading-[1.05] sm:text-6xl xl:text-7xl">
            {hero.headline.map((line) => (
              <span key={line} className="block">
                {line}
              </span>
            ))}
          </h1>
          <p className="mt-5 max-w-md text-lg text-ink-soft">{hero.subcopy}</p>
          <div className="mt-8 flex flex-col gap-3 sm:flex-row">
            <ButtonLink href={hero.primaryCta.href} size="lg" className="w-full sm:w-auto">
              {hero.primaryCta.label}
            </ButtonLink>
            <ButtonLink href={hero.secondaryCta.href} variant="secondary" size="lg" className="w-full sm:w-auto">
              {hero.secondaryCta.label}
            </ButtonLink>
          </div>
        </div>
        <HeroVisual images={hero.images} />
      </Container>
    </section>
  );
}
