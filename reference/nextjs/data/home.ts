import { findPublicImage } from "@/lib/local-images";
import type { ImageAsset } from "@/types/product";

const image = (base: string, alt: string): ImageAsset | undefined => {
  const src = findPublicImage(base);
  return src ? { src, alt } : undefined;
};

/**
 * Homepage copy and brand imagery. Headline, sub-copy, CTAs and section names come from the brief;
 * everything else is draft copy that must be approved by the business.
 * Images: save files as public/images/brand/hero.(webp|png|jpg) and story.(webp|png|jpg).
 */
export const homeContent = {
  hero: {
    headline: "Every Bite, Full of Happiness.",
    subcopy: "Discover delicious snacks made for every moment.",
    primaryCta: { label: "Shop Now", href: "/shop" },
    secondaryCta: { label: "Explore Products", href: "#categories" },
    image: image("images/brand/hero", "Priniti Foods snacks"),
  },
  promo: {
    headline: "More Crunch. More Value.",
    description: "Value packs and combos for your favourite snacks are on the way.",
    badge: "Coming soon",
    cta: { label: "Shop Combos", href: "/category/combos" }, // Combos is ecommerce-only and unpublished until combo products exist
  },
  why: {
    heading: "Why Priniti Foods",
    items: [
      { icon: "wheat", title: "Quality Ingredients", text: "Snacks made with care, from the ingredients up." },
      { icon: "smile", title: "Great Taste", text: "Flavours that are made for sharing." },
      { icon: "sparkles", title: "Hygienic Manufacturing", text: "Made in a facility run with hygiene in mind." },
      { icon: "handshake", title: "Trusted Brand", text: "An established Indian snacks manufacturer." },
      { icon: "grid", title: "Wide Product Range", text: "Namkeen to cookies, chips to rusk, all in one place." },
    ],
  },
  brandStory: {
    heading: "Snacks made for every moment",
    paragraphs: [
      "Priniti Foods Pvt. Ltd. is an established Indian FMCG manufacturer with a wide portfolio of snacks, from namkeen and chips to sweets, cookies and rusk.",
      "Every pack carries the same promise: Swad Mein No. 1.",
    ],
    cta: { label: "Know Our Story", href: "/about" },
    image: image("images/brand/story", "Priniti Foods brand story"),
  },
  reviews: {
    heading: "What customers say",
    emptyTitle: "No reviews yet",
    emptyText: "Customer reviews will appear here once customers start sharing them.",
  },
  social: {
    heading: "Follow the snacking",
    description: "Find Priniti Foods on social media.",
    tileCount: 6,
  },
  newsletter: {
    headline: "Stay in the Snack Loop",
    copy: "New launches and offers, straight to your inbox.",
  },
};

export type WhyIconKey = "quality" | "taste" | "hygiene" | "trust" | "range";

export interface Cta {
  label: string;
  href: string;
}

export interface HomeData {
  hero: { headline: string[]; subcopy: string; primaryCta: Cta; secondaryCta: Cta; images: (ImageAsset | undefined)[] };
  promo: { headline: string; copy: string; cta: Cta; image?: ImageAsset };
  why: { icon: WhyIconKey; title: string; text: string }[];
  story: { title: string; paragraphs: string[]; cta: Cta; images: (ImageAsset | undefined)[] };
  newsletter: { title: string; text: string };
  social: { title: string; text: string; tiles: number };
}

const whyIconKeys: Record<string, WhyIconKey> = { wheat: "quality", smile: "taste", sparkles: "hygiene", handshake: "trust", grid: "range" };

/**
 * Shape consumed by components/home/*. Derived from homeContent so copy lives in one place.
 * Images: public/images/brand/hero-1..3, promo, story-1..2 (webp, png or jpg).
 */
export const home: HomeData = {
  hero: {
    headline: homeContent.hero.headline.split(/(?<=,)\s+/),
    subcopy: homeContent.hero.subcopy,
    primaryCta: homeContent.hero.primaryCta,
    secondaryCta: homeContent.hero.secondaryCta,
    images: [1, 2, 3].map((n) => image(`images/brand/hero-${n}`, "Priniti Foods snacks")),
  },
  promo: {
    headline: homeContent.promo.headline,
    copy: homeContent.promo.description,
    cta: homeContent.promo.cta,
    image: image("images/brand/promo", "Priniti Foods combo packs"),
  },
  why: homeContent.why.items.map((i) => ({ icon: whyIconKeys[i.icon] ?? "quality", title: i.title, text: i.text })),
  story: {
    title: homeContent.brandStory.heading,
    paragraphs: homeContent.brandStory.paragraphs,
    cta: homeContent.brandStory.cta,
    images: [1, 2].map((n) => image(`images/brand/story-${n}`, "Priniti Foods brand story")),
  },
  newsletter: { title: homeContent.newsletter.headline, text: homeContent.newsletter.copy },
  social: { title: homeContent.social.heading, text: homeContent.social.description, tiles: homeContent.social.tileCount },
};
