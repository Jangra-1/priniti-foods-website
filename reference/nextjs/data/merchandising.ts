/**
 * Hand-curated homepage merchandising. Every slug must be a PUBLISHED product (check:catalog enforces it).
 * Labels are deliberately neutral: there is no sales or "new product" data, so nothing here claims
 * "best selling", "popular" or "new". Edit the slug lists to change what the homepage shows.
 */
export const merchandising = {
  heroSlugs: ["chips-classic-salted", "charchare-tangy-tomato", "ringo-star-tangy-tomato", "popcorn-butter-salted", "jungle-masti"],
  storySlugs: ["jungle-masti", "all-in-one", "gulab-jamun"],
  curatedPicks: {
    title: "Curated picks",
    description: "A hand-picked selection from across the Priniti range.",
    slugs: ["all-in-one", "chips-classic-salted", "charchare-tangy-tomato", "ringo-star-tangy-tomato", "jungle-masti", "popcorn-butter-salted", "ajwain-cookies", "gulab-jamun"],
  },
  explore: {
    title: "Explore the range",
    description: "From namkeen to cookies, a taste of every category.",
    slugs: ["bombay-mix", "chips-tomato-punch", "charchare-mast-masala", "pizza", "rusk", "besan-ladoo", "jeera-cookies", "soan-papdi"],
  },
  sweetsBakery: {
    title: "Sweets & bakery",
    description: "Mithai, cookies and rusk for tea-time.",
    href: "/category/sweets",
    slugs: ["rasgulla", "besan-ladoo", "atta-cookies", "coconut-cookies"],
  },
  featured: { slug: "gulab-jamun" },
} as const;
