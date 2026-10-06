/**
 * Central site configuration. Everything here is a placeholder to be confirmed
 * with the business; nothing in components should hardcode these values.
 */
export const siteConfig = {
  name: "Priniti Foods",
  legalName: "Priniti Foods Pvt. Ltd.",
  url: process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000",
  /** Optional announcement-bar text. Hidden until the business supplies a confirmed message. */
  announcement: undefined as string | undefined,

  description:
    "Namkeen, chips, puffs, popcorn, sweets, cookies, rusk and combo packs from Priniti Foods.",

  /** Original Priniti logo (converted from the supplied CorelDRAW file). */
  logo: { src: "/logo/priniti-logo.svg", width: 346, height: 200 } as undefined | { src: string; width: number; height: number },

  /** Indian food sellers typically need to show their FSSAI licence number. Add when available. */
  fssaiLicense: undefined as string | undefined,

  commerce: {
    currency: "INR",
    /** PENDING: shipping rules are not confirmed. The brief's example (₹499) was never confirmed, so it is unset. */
    freeShippingThreshold: undefined as number | undefined,
    maxQuantityPerLine: 10, // UI limit only; real limits come from inventory later
  },

  catalog: { pageSize: 8 }, // UI setting for listing pages; small on purpose so pagination is visible with sample data

  /** PENDING: set `href` to the real profile URL. Entries without an href are not rendered as links. */
  social: [
    { label: "Instagram", href: undefined as string | undefined, icon: "instagram" },
    { label: "Facebook", href: undefined as string | undefined, icon: "facebook" },
    { label: "YouTube", href: undefined as string | undefined, icon: "youtube" },
    { label: "X (Twitter)", href: undefined as string | undefined, icon: "twitter" },
  ],
} as const;

export type SocialIcon = (typeof siteConfig.social)[number]["icon"];
