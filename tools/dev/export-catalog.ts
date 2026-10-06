/**
 * LOCAL PREVIEW ONLY. Writes the reference catalog in the theme's view-model shape (inc/catalog.php) to
 * tools/dev/catalog.json, for tools/dev/local-preview-mu.php. `--test-prices` layers the reference's development
 * TEST prices on, to preview the priced UI (cart, quick view, buy now). Never used on a real site.
 */
import { writeFileSync } from "node:fs";
import { categories } from "../../reference/nextjs/data/categories.ts";
import { comboProducts } from "../../reference/nextjs/data/combos.ts";
import { products } from "../../reference/nextjs/data/products.ts";
import { applyTestPricing } from "../../reference/nextjs/data/test-prices.ts";

const base = process.argv.includes("--base") ? process.argv[process.argv.indexOf("--base") + 1] : "http://localhost:8080";
const test = process.argv.includes("--test-prices");
const list = test ? applyTestPricing([...products, ...comboProducts]) : [...products, ...comboProducts];
const order = new Map(categories.map((c, i) => [c.slug, i]));

let nextId = 1000;
const catalog = list.map((p, i) => {
  const id = 100 + i;
  const variants = p.variants.map((v) => ({
    id: p.variants.length > 1 ? nextId++ : id,
    label: v.source === "test-placeholder" ? "" : v.label,
    source: v.source,
    price: v.price ?? null,
    mrp: v.mrp ?? v.price ?? null,
    inStock: v.inStock ?? null,
    sku: v.sku ?? "",
    attributes: p.variants.length > 1 ? [{ attribute: "Pack size", value: v.label }] : [],
    purchasable: v.price !== undefined && v.inStock !== false,
  }));
  return {
    id,
    slug: p.slug,
    name: p.name,
    href: `${base}/product/${p.slug}/`,
    categorySlug: p.categorySlug,
    categoryName: p.categoryName,
    categoryHref: `${base}/category/${p.categorySlug}/`,
    categoryOrder: order.get(p.categorySlug) ?? 99,
    menuOrder: 0,
    images: p.images.map((img) => ({ src: `${base}/ref${img.src}`, thumb: `${base}/ref${img.src}`, srcset: "", alt: img.alt })),
    variants,
    rating: p.reviewCount ? (p.rating ?? null) : null,
    reviewCount: p.reviewCount ?? 0,
    badges: (p.badges ?? []).filter((b) => b !== "combo"),
    featured: !!p.featured,
    date: 0,
    description: p.description ?? null,
    highlights: p.highlights ?? null,
    ingredients: p.ingredients ?? null,
    nutrition: p.nutrition ?? null,
    storage: p.storage ?? null,
    shippingNote: p.shippingNote ?? null,
    faqs: p.faqs ?? null,
  };
});

const cats = categories.map((c, i) => {
  const cover = list.find((p) => p.slug === c.coverProductSlug)?.images[0];
  return {
    id: 10 + i,
    slug: c.slug,
    name: c.name,
    description: c.description ?? "",
    href: `${base}/category/${c.slug}/`,
    image: cover ? { src: `${base}/ref${cover.src}`, alt: cover.alt } : null,
    order: i,
    published: c.published !== false,
  };
});

writeFileSync("tools/dev/catalog.json", JSON.stringify({ catalog, categories: cats }, null, 1));
console.log(`tools/dev/catalog.json: ${catalog.length} products, ${cats.length} categories${test ? " (TEST prices)" : ""}`);
