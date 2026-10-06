import { categories } from "@/data/categories";
import type { PackSource, PackVariant, Product } from "@/types/product";

/**
 * PUBLISHED CATALOG: 57 products confirmed by BOTH the old website's product structure and a supplied pack image.
 * Not here: unresolved matches, image-only items and website-only items (see data/pending-products.ts).
 *
 * Every optional field is PENDING (not invented): price, MRP, SKU, ingredients, nutrition, description, rating.
 * Pack-size variants exist only where verified; `source` records how.
 * Images: public/images/products/<slug>-<n>.webp, web-ready copies of the supplied packs (artwork unchanged).
 */
interface Row {
  slug: string;
  name: string;
  category: string;
  images: number;
  packs?: [label: string, source: PackSource][];
  note?: string;
  imageAlts?: string[];
}

const rows: Row[] = [
  { slug: "all-in-one", name: "All In One", category: "indian-traditional-namkeen", images: 1 },
  { slug: "aloo-bhujia", name: "Aloo Bhujia", category: "indian-traditional-namkeen", images: 1 },
  { slug: "bombay-mix", name: "Bombay Mix", category: "indian-traditional-namkeen", images: 1 },
  { slug: "boondi", name: "Boondi", category: "indian-traditional-namkeen", images: 1 },
  { slug: "chana-jor-garam", name: "Chana Jor Garam", category: "indian-traditional-namkeen", images: 1 },
  { slug: "chatpati-dal", name: "Chatpati Dal", category: "indian-traditional-namkeen", images: 1 },
  { slug: "diet-chiwda", name: "Diet Chiwda", category: "indian-traditional-namkeen", images: 1 },
  { slug: "diet-mixture", name: "Diet Mixture", category: "indian-traditional-namkeen", images: 1 },
  { slug: "gathiya", name: "Gathiya", category: "indian-traditional-namkeen", images: 1 },
  { slug: "gathiya-papdi", name: "Gathiya Papdi", category: "indian-traditional-namkeen", images: 1 },
  { slug: "hing-jeera-chana", name: "Hing Jeera Chana", category: "indian-traditional-namkeen", images: 1 },
  { slug: "jhalmuri", name: "Jhalmuri", category: "indian-traditional-namkeen", images: 1 },
  { slug: "kaju-mixture", name: "Kaju Mixture", category: "indian-traditional-namkeen", images: 1 },
  { slug: "khatta-meetha", name: "Khatta Meetha", category: "indian-traditional-namkeen", images: 1 },
  { slug: "malai-sev", name: "Malai Sev", category: "indian-traditional-namkeen", images: 1 },
  { slug: "masala-murmura", name: "Masala Murmura", category: "indian-traditional-namkeen", images: 1 },
  { slug: "mast-matar", name: "Mast Matar", category: "indian-traditional-namkeen", images: 1 },
  { slug: "moong-dal", name: "Moong Dal", category: "indian-traditional-namkeen", images: 1 },
  { slug: "navratan-mixture", name: "Navratan Mixture", category: "indian-traditional-namkeen", images: 1 },
  { slug: "panchrattan", name: "Panchrattan", category: "indian-traditional-namkeen", images: 1 },
  { slug: "punjabi-tadka", name: "Punjabi Tadka", category: "indian-traditional-namkeen", images: 1 },
  { slug: "ratlami-sev", name: "Ratlami Sev", category: "indian-traditional-namkeen", images: 1 },
  { slug: "tasty-nuts", name: "Tasty Nuts", category: "indian-traditional-namkeen", images: 1 },
  { slug: "tikha-mitha-mix", name: "Tikha Mitha Mix", category: "indian-traditional-namkeen", images: 1 },
  { slug: "chips-classic-salted", name: "Potato Chips Classic Salted", category: "potato-chips", images: 1 },
  { slug: "chips-cream-n-onion", name: "Potato Chips Cream 'n' Onion", category: "potato-chips", images: 1 },
  { slug: "chips-masala-punch", name: "Potato Chips Masala Punch", category: "potato-chips", images: 1 },
  { slug: "chips-tomato-punch", name: "Potato Chips Tomato Punch", category: "potato-chips", images: 1 },
  { slug: "charchare-mast-masala", name: "CharChare Mast Masala", category: "charchare-sticks", images: 1 },
  { slug: "charchare-tangy-tomato", name: "CharChare Tangy Tomato", category: "charchare-sticks", images: 1 },
  { slug: "popcorn-butter-salted", name: "Popcorn Butter Salted", category: "popcorn", images: 1 },
  { slug: "a-to-z", name: "A TO Z", category: "puffs-fryums", images: 1 },
  { slug: "dal-chawal", name: "Dal Chawal", category: "puffs-fryums", images: 1, note: "Appeared only once in the old site menu; confirm category." },
  { slug: "jungle-masti", name: "Jungle Masti", category: "puffs-fryums", images: 1 },
  { slug: "masala-noodles", name: "Masala Noodles", category: "puffs-fryums", images: 1, note: "Old site menu placement was ambiguous; image matched by the name printed on the pack." },
  { slug: "moon-chips", name: "Moon Chips", category: "puffs-fryums", images: 1, note: "Pack prints 'Moon Chips Katori'; relationship to website item 'Tomato Katori' is unclear." },
  { slug: "noodles-yellow", name: "Noodles (Yellow)", category: "puffs-fryums", images: 1 },
  { slug: "pasta", name: "Pasta", category: "puffs-fryums", images: 1 },
  { slug: "pizza", name: "Pizza", category: "puffs-fryums", images: 1 },
  { slug: "puff-tangy-tomato", name: "Puff Tangy Tomato", category: "puffs-fryums", images: 1 },
  { slug: "salted-pipe", name: "Salted Pipe", category: "puffs-fryums", images: 1 },
  { slug: "samosa-sev", name: "Samosa Sev", category: "puffs-fryums", images: 1, note: "Pack reads Namkeen; old site menu lists it under Puffs & Fryums." },
  { slug: "ringo-star-tangy-tomato", name: "Ringo Star Tangy Tomato", category: "ringo-star-rings", images: 1 },
  { slug: "rusk", name: "Rusk", category: "rusk", images: 1, note: "A blog mentions Elaichi/Suji rusk; only one pack supplied." },
  { slug: "besan-ladoo", name: "Besan Ladoo", category: "sweets", images: 1, packs: [["400 g", "image-filename"]], note: "400 g comes from the image filename, not read on the pack. Pack prints 'Dry Fruit Besan Ladoo'." },
  { slug: "gulab-jamun", name: "Gulab Jamun", category: "sweets", images: 2, packs: [["1 Kg", "pack-art"]], imageAlts: ["Gulab Jamun tin, front", "Gulab Jamun gift carton"], note: "1 Kg / 35.3 oz is printed on the tin and carton. Image 1 is the tin, image 2 the gift carton." },
  { slug: "panjeeri-ladoo", name: "Panjeeri Ladoo", category: "sweets", images: 1, packs: [["400 g", "image-filename"]], note: "400 g comes from the image filename, not read on the pack. Pack prints 'Dry Fruit Panjeeri Ladoo'." },
  { slug: "rasgulla", name: "Rasgulla", category: "sweets", images: 2, packs: [["1 Kg", "pack-art"]], imageAlts: ["Rasgulla tin, front", "Rasgulla gift carton"], note: "1 Kg / 35.3 oz is printed on the tin and carton. Image 1 is the tin, image 2 the gift carton." },
  { slug: "soan-papdi", name: "Soan Papdi", category: "sweets", images: 1, packs: [["400 g", "image-filename"]], note: "400 g comes from the image filename, not read on the pack." },
  { slug: "ajwain-cookies", name: "Ajwain Cookies", category: "cookies", images: 1, packs: [["200 g", "website"], ["300 g", "website"]], note: "One image supplied; it does not show which pack size it depicts." },
  { slug: "atta-cookies", name: "Atta Cookies", category: "cookies", images: 1, packs: [["200 g", "website"], ["300 g", "website"]], note: "One image supplied; it does not show which pack size it depicts." },
  { slug: "badam-cookies", name: "Badam Cookies", category: "cookies", images: 1, packs: [["300 g", "website"]], note: "One image supplied; it does not show which pack size it depicts." },
  { slug: "coconut-cookies", name: "Coconut Cookies", category: "cookies", images: 1, packs: [["200 g", "website"], ["300 g", "website"]], note: "One image supplied; it does not show which pack size it depicts. The Coconut Cookies product page lists 300 g only, but the category page lists 200 g and 300 g; confirm." },
  { slug: "jam-cookies", name: "Jam Cookies", category: "cookies", images: 1, packs: [["200 g", "website"], ["300 g", "website"]], note: "One image supplied; it does not show which pack size it depicts." },
  { slug: "jeera-cookies", name: "Jeera Cookies", category: "cookies", images: 1, packs: [["200 g", "website"], ["300 g", "website"]], note: "One image supplied; it does not show which pack size it depicts." },
  { slug: "kaju-cookies", name: "Kaju Cookies", category: "cookies", images: 1, packs: [["300 g", "website"]], note: "One image supplied; it does not show which pack size it depicts." },
  { slug: "tutti-frutti-cookies", name: "Tutti Frutti Cookies", category: "cookies", images: 1, packs: [["200 g", "website"], ["300 g", "website"]], note: "One image supplied; it does not show which pack size it depicts." },
];

const categoryName = (slug: string) => categories.find((c) => c.slug === slug)?.name ?? slug;

function toProduct(r: Row): Product {
  const variants: PackVariant[] = (r.packs ?? []).map(([label, source]) => ({
    id: `${r.slug}-${label.toLowerCase().replace(/[^a-z0-9]+/g, "")}`,
    label,
    source,
  }));
  return {
    id: `prn-${r.slug}`,
    slug: r.slug,
    name: r.name,
    categorySlug: r.category,
    categoryName: categoryName(r.category),
    images: Array.from({ length: r.images }, (_, i) => ({
      src: `/images/products/${r.slug}-${i + 1}.webp`,
      alt: r.imageAlts?.[i] ?? `${r.name} pack by Priniti Foods`,
    })),
    variants,
    internalNotes: r.note ? [r.note] : undefined,
  };
}

export const products: Product[] = rows.map(toProduct);
