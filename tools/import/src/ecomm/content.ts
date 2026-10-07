import { readFileSync } from "node:fs";
import path from "node:path";
import { wpGetAll, wpSend } from "../http.ts";
import { META } from "../meta-keys.ts";

/**
 * Product information from the official product pages (tools/import/data/official-content.json, built by
 * tools/import/scripts/build-official-content.py). Writes ONLY content fields: description, short description,
 * highlights, ingredients and storage. Prices, variations, pack data, images and categories are never sent.
 * Fields the official site does not publish are left empty, and the storefront shows "Information coming soon".
 */

export const CONTENT_FILE = path.resolve(import.meta.dirname, "../../data/official-content.json");

export interface OfficialContent {
  source: string;
  officialTitle: string;
  shortDescription: string | null;
  description: string | null;
  highlights: string[];
  ingredients: string | null;
  allergenNote: string | null;
  storage: string | null;
  shelfLife: string | null;
}

export const loadContent = () => (JSON.parse(readFileSync(CONTENT_FILE, "utf8")) as { products: Record<string, OfficialContent> }).products;

const esc = (s: string) => s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

/** WooCommerce fields for one product. Pure, so the shape is unit-tested. */
export function contentPayload(c: OfficialContent) {
  const ingredients = [c.ingredients ? `${c.ingredients}.` : "", c.allergenNote ?? ""].filter(Boolean).join(" ");
  const storage = [c.storage ?? "", c.shelfLife ? `Shelf life: ${c.shelfLife.charAt(0).toLowerCase()}${c.shelfLife.slice(1)}.` : ""].filter(Boolean).join(" ");
  return {
    description: c.description ? `<p>${esc(c.description)}</p>` : "",
    short_description: c.shortDescription ? `<p>${esc(c.shortDescription)}</p>` : "",
    meta_data: [
      { key: META.highlights, value: c.highlights },
      { key: META.ingredients, value: ingredients },
      { key: META.storage, value: storage },
      { key: META.contentSource, value: c.source },
    ],
  };
}

interface WcProduct {
  id: number;
  name: string;
}

export async function applyContent(opts: { dryRun: boolean }) {
  const content = loadContent();
  const products = await wpGetAll<WcProduct>("/wc/v3/products?status=any&context=edit");
  let updated = 0;
  for (const [name, c] of Object.entries(content)) {
    const p = products.find((x) => x.name.replace(/&#8217;|&#039;/g, "'").toLowerCase() === name.toLowerCase());
    if (!p) {
      console.log(`${name}: no matching product, skipped`);
      continue;
    }
    const body = contentPayload(c);
    const filled = [body.description && "description", body.short_description && "short", c.highlights.length && `${c.highlights.length} highlights`, c.ingredients && "ingredients", c.storage && "storage", c.shelfLife && "shelf life"].filter(Boolean);
    console.log(`${opts.dryRun ? "[dry-run] " : ""}#${p.id} ${name}: ${filled.join(", ")}`);
    if (!opts.dryRun) await wpSend("PUT", `/wc/v3/products/${p.id}`, body);
    updated++;
  }
  const without = products.filter((p) => !Object.keys(content).some((n) => n.toLowerCase() === p.name.replace(/&#8217;|&#039;/g, "'").toLowerCase()));
  console.log(`${opts.dryRun ? "[dry-run] " : ""}products updated: ${updated}; without official content: ${without.map((p) => p.name).join(", ")}`);
}
