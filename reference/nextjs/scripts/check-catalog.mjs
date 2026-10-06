#!/usr/bin/env node
/**
 * Data-safety checks for the catalog. Run: npm run check:catalog
 * 1. Published and pending catalogs never share a slug.
 * 2. Every published image exists on disk and every product has at least one.
 * 3. No published product carries a price/SKU/ingredient/nutrition field (all PENDING until supplied).
 * 4. Nothing in the storefront imports the pending list.
 */
import { readFileSync, existsSync, readdirSync, statSync } from "node:fs";
import { dirname, join, resolve } from "node:path";
import { fileURLToPath } from "node:url";

// Project root = the folder above /scripts, resolved from this file's own location.
// fileURLToPath() converts the file URL into a proper OS path (on Windows "file:///C:/x" becomes "C:\x"),
// so no path is hard-coded and it works from any working directory.
const root = resolve(dirname(fileURLToPath(import.meta.url)), "..");
const read = (p) => readFileSync(join(root, p), "utf8");
const fail = [];

const published = [...read("data/products.ts").matchAll(/\{ slug: "([^"]+)", name: "[^"]+", category: "([^"]+)", images: (\d+)/g)];
const pending = [...read("data/pending-products.ts").matchAll(/suggestedSlug: "([^"]+)"/g)].map((m) => m[1]);
const categories = new Set([...read("data/categories.ts").matchAll(/slug: "([^"]+)", name:/g)].map((m) => m[1]));

const slugs = published.map((m) => m[1]);
if (new Set(slugs).size !== slugs.length) fail.push("Duplicate published slugs");
const overlap = slugs.filter((s) => pending.includes(s));
if (overlap.length) fail.push(`Published/pending overlap: ${overlap.join(", ")}`);

for (const [, slug, category, count] of published) {
  if (!categories.has(category)) fail.push(`${slug}: unknown category ${category}`);
  if (Number(count) < 1) fail.push(`${slug}: no image`);
  for (let i = 1; i <= Number(count); i++) {
    if (!existsSync(join(root, `public/images/products/${slug}-${i}.webp`))) fail.push(`${slug}: missing image ${i}`);
  }
}

const productsSrc = read("data/products.ts");
for (const field of ["price:", "mrp:", "sku:", "ingredients:", "nutrition:", "inStock:", "rating:"]) {
  if (new RegExp(`\\b${field}`).test(productsSrc.replace(/\/\*[\s\S]*?\*\//g, "").replace(/\/\/.*$/gm, ""))) {
    fail.push(`data/products.ts contains "${field}" — only add it with real supplied data`);
  }
}

const walk = (dir) =>
  readdirSync(join(root, dir)).flatMap((f) => {
    const rel = `${dir}/${f}`;
    return statSync(join(root, rel)).isDirectory() ? walk(rel) : [rel];
  });
for (const dir of ["app", "components", "lib", "hooks", "store"]) {
  for (const f of walk(dir).filter((f) => /\.(ts|tsx)$/.test(f))) {
    if (/from\s+["'][^"']*pending-products["']/.test(read(f))) fail.push(`${f} imports the pending list`);
  }
}

// 5. Homepage merchandising may only reference PUBLISHED products.
const stripComments = (t) => t.replace(/\/\*[\s\S]*?\*\//g, "").replace(/\/\/.*$/gm, "");
const merchSlugs = [...stripComments(read("data/merchandising.ts")).matchAll(/"([a-z0-9]+(?:-[a-z0-9]+)+|[a-z0-9]+)"/g)].map((m) => m[1]);
for (const slug of merchSlugs) {
  if (!slugs.includes(slug)) fail.push(`data/merchandising.ts references "${slug}", which is not a published product`);
}

// 6. TEST prices: isolated, flagged, never an MRP, and only layered on in lib/api/products.ts.
const testPrices = read("data/test-prices.ts");
if (!/isTestPrice:\s*true/.test(testPrices)) fail.push("data/test-prices.ts must flag every price with isTestPrice: true");
if (/\bmrp\s*:/.test(stripComments(testPrices))) fail.push("data/test-prices.ts must never set an mrp");
for (const dir of ["app", "components", "hooks", "store", "lib"]) {
  for (const f of walk(dir).filter((f) => /\.(ts|tsx)$/.test(f))) {
    if (f !== "lib/api/products.ts" && /from\s+["'][^"']*data\/test-prices["']/.test(read(f))) fail.push(`${f} imports data/test-prices (only lib/api/products.ts may)`);
  }
}
if (!/NODE_ENV\s*===\s*"development"/.test(read("data/test-pricing.config.ts"))) fail.push("test pricing must default to OFF outside development");
if (!/!v\.isTestPrice/.test(read("lib/seo.ts"))) fail.push("lib/seo.ts must exclude TEST prices from structured data");

// 7. Combos: ecommerce-only, empty until real combos exist; any combo may only contain published products.
const combosSrc = stripComments(read("data/combos.ts"));
for (const m of combosSrc.matchAll(/productSlug:\s*"([^"]+)"/g)) {
  if (!slugs.includes(m[1])) fail.push(`data/combos.ts includes unpublished product "${m[1]}"`);
}
const comboCount = (combosSrc.match(/categorySlug:\s*"combos"/g) ?? []).length;

console.log(`published: ${slugs.length}, pending: ${pending.length}, categories: ${categories.size}, combo products: ${comboCount}`);
if (fail.length) {
  console.error("FAILED:\n- " + fail.join("\n- "));
  process.exit(1);
}
console.log("catalog checks passed");
