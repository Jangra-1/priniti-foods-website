import Image from "next/image";
import { Instagram } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { ButtonLink } from "@/components/ui/Button";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { homeContent } from "@/data/home";
import { siteConfig } from "@/data/site";
import type { Product } from "@/types/product";

/**
 * Social section. No real posts or follower data exist, so the tiles are decorative brand tiles
 * (real packs, not presented as posts) and profile buttons only render for URLs set in siteConfig.
 */
export function SocialGrid({ products }: { products: Product[] }) {
  const { heading, description, tileCount } = homeContent.social;
  const tiles = products.filter((p) => p.images[0]).slice(0, tileCount);
  const links = siteConfig.social.filter((s) => s.href);
  return (
    <section aria-labelledby="social-heading" className="bg-surface py-8 lg:py-10">
      <Container>
        <SectionHeading id="social-heading" eyebrow="Stay connected" title={heading} description={description} align="center" className="mb-5" />
        <ul role="list" aria-hidden className="grid grid-cols-3 gap-2 sm:gap-3 lg:grid-cols-6">
          {tiles.map((p) => (
            <li key={p.slug}>
              <div className="relative aspect-square overflow-hidden rounded-xl bg-blush ring-1 ring-line/60">
                <Image src={p.images[0].src} alt="" fill sizes="(min-width:1024px) 14vw, 30vw" className="object-contain p-3" />
              </div>
            </li>
          ))}
        </ul>
        <div className="mt-5 flex flex-wrap items-center justify-center gap-3">
          {links.length > 0 ? (
            links.map((s) => (
              <ButtonLink key={s.label} href={s.href as string} variant="outline" size="md" target="_blank" rel="noopener noreferrer">
                <Instagram className="size-4" aria-hidden />
                {s.label}
              </ButtonLink>
            ))
          ) : (
            <p className="text-sm text-ink-soft">Social profile links are coming soon.</p>
          )}
        </div>
      </Container>
    </section>
  );
}
