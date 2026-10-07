/**
 * LOCAL PREVIEW ONLY. Writes the reference catalog in the theme's view-model shape (inc/catalog.php) to
 * tools/dev/catalog.json, for tools/dev/local-preview-mu.php. `--test-prices` layers the reference's development
 * TEST prices on, to preview the priced UI (cart, quick view, buy now). `--ecomm` builds the catalog the way the live
 * store now has it: the e-commerce item list reconciled with the reference products (same packs, prices and images).
 * Never used on a real site.
 */
import { writeFileSync } from "node:fs";
import { categories } from "../../reference/nextjs/data/categories.ts";
import { comboProducts } from "../../reference/nextjs/data/combos.ts";
import { products } from "../../reference/nextjs/data/products.ts";
import { applyTestPricing } from "../../reference/nextjs/data/test-prices.ts";
import { RENAMES } from "../import/src/ecomm/apply.ts";
import { loadItems } from "../import/src/ecomm/plan.ts";
import { reconcile } from "../import/src/ecomm/reconcile.ts";

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

let out = catalog;
if (process.argv.includes("--ecomm")) {
  // Reference products stand in for the live catalog (the live store was imported from them).
  const asLive = catalog.map((p) => ({ id: p.id, name: p.name, slug: p.slug, type: p.variants.length > 1 ? "variable" : "simple", status: "publish", category: p.categorySlug, imageCount: p.images.length, packSize: "", variations: [] }));
  const r = reconcile(loadItems(), asLive);
  if (r.problems.length) throw new Error(r.problems.join("\n"));
  let newId = 5000;
  out = r.products.map((planned) => {
    const ref = catalog.find((p) => p.id === planned.live?.id);
    const id = ref?.id ?? newId++;
    const slug = ref?.slug ?? planned.name.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/(^-|-$)/g, "");
    const category = cats.find((c) => c.slug === planned.category)!;
    return {
      ...(ref ?? { ...catalog[0], images: [], description: null, badges: [], featured: false, highlights: null, ingredients: null, nutrition: null, storage: null, shippingNote: null, faqs: null, rating: null, reviewCount: 0 }),
      id,
      slug,
      name: (ref && RENAMES[ref.id]) || planned.name,
      href: `${base}/product/${slug}/`,
      categorySlug: category.slug,
      categoryName: category.name,
      categoryHref: category.href,
      categoryOrder: category.order,
      variants: planned.packs.map((k, i) => ({
        id: planned.packs.length > 1 ? nextId++ : id,
        label: k.label,
        source: "ecomm-item-list",
        price: k.price,
        mrp: k.price,
        inStock: null,
        sku: "",
        attributes: planned.packs.length > 1 ? [{ attribute: "Pack size", value: k.label }] : [],
        purchasable: true,
        pcs: k.pcs,
        unitMrp: k.mrp,
        weight: k.weight,
        order: i,
      })),
    };
  });
  // Reference products the sheet does not list stay visible locally, unpriced, as on the live store.
  for (const p of catalog) if (!r.products.some((x) => x.live?.id === p.id) && p.categorySlug !== "combos") out.push({ ...p, variants: p.variants.map((v) => ({ ...v, price: null, mrp: null, purchasable: false })) });
}

writeFileSync("tools/dev/catalog.json", JSON.stringify({ catalog: out, categories: cats }, null, 1));
console.log(`tools/dev/catalog.json: ${out.length} products, ${cats.length} categories${test ? " (TEST prices)" : ""}${process.argv.includes("--ecomm") ? " (e-commerce item list)" : ""}`);
