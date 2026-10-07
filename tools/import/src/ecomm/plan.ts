import { readFileSync, writeFileSync } from "node:fs";
import path from "node:path";
import { wpGetAll } from "../http.ts";
import { type LiveProduct, money, packTotal, parseItems } from "./model.ts";
import { type Reconciliation, reconcile } from "./reconcile.ts";

export const ITEM_LIST = path.resolve(import.meta.dirname, "../../data/ecomm-item-list.csv");

interface WcProduct {
  id: number;
  name: string;
  slug: string;
  type: string;
  status: string;
  categories: { slug: string }[];
  images: unknown[];
  meta_data: { key: string; value: unknown }[];
}
interface WcVariation {
  id: number;
  attributes: { name: string; option: string }[];
}

/** Read-only snapshot of the live catalog in the shape the reconciliation needs. */
export async function fetchLive(): Promise<LiveProduct[]> {
  const products = await wpGetAll<WcProduct>("/wc/v3/products?status=any&context=edit");
  const out: LiveProduct[] = [];
  for (const p of products) {
    const variations = p.type === "variable" ? await wpGetAll<WcVariation>(`/wc/v3/products/${p.id}/variations?context=edit`) : [];
    out.push({
      id: p.id,
      name: p.name.replace(/&#8217;|&#039;/g, "'").replace(/&amp;/g, "&"),
      slug: p.slug,
      type: p.type,
      status: p.status,
      category: p.categories[0]?.slug ?? "",
      imageCount: p.images.length,
      packSize: String(p.meta_data.find((m) => m.key === "_priniti_pack_size")?.value ?? ""),
      variations: variations.map((v) => ({ id: v.id, label: v.attributes.map((a) => a.option).join(" · ") })),
    });
  }
  return out;
}

export function loadItems() {
  return parseItems(readFileSync(ITEM_LIST, "utf8"));
}

/** Markdown reconciliation report (sheet row by row, decisions, prices, live products not in the sheet). */
export function report(r: Reconciliation): string {
  const L: string[] = [];
  const created = r.products.filter((p) => !p.live);
  const matched = r.products.filter((p) => p.live);
  const packs = r.products.flatMap((p) => p.packs);
  L.push("# E-commerce catalog reconciliation", "");
  L.push(`Source: tools/import/data/ecomm-item-list.csv (Ecomm Item List.xlsx, sheet 30-09-2025)`, "");
  L.push("| | Count |", "|---|---|");
  L.push(`| Sheet item rows | ${r.totalRows} |`);
  L.push(`| Excluded rows (Potato Chips Sizzling Hot) | ${r.excludedRows.length} |`);
  L.push(`| Rows in the e-commerce catalog | ${r.totalRows - r.excludedRows.length} |`);
  L.push(`| Products (rows grouped by product) | ${r.products.length} |`);
  L.push(`| Matched to existing WooCommerce products | ${matched.length} |`);
  L.push(`| New products to create | ${created.length} |`);
  L.push(`| Sellable packs / variations | ${packs.length} (${packs.filter((p) => p.multiPack).length} single packs with 1/2/3 selector, ${packs.filter((p) => !p.multiPack).length} Pack-of-X) |`);
  L.push(`| Products with more than one pack size | ${r.products.filter((p) => p.type === "variable").length} |`);
  L.push(`| Existing simple products becoming variable | ${r.products.filter((p) => p.convertToVariable).length} |`);
  L.push(`| Live products not in the sheet | ${r.unmatchedLive.length} |`);
  L.push(`| Problems | ${r.problems.length} |`, "");

  if (r.problems.length) L.push("## Problems (must be zero before apply)", "", ...r.problems.map((p) => `- ${p}`), "");

  L.push("## Excluded", "", ...r.excludedRows.map((x) => `- line ${x.line}: ${x.itemName}`), "");

  L.push("## Products", "");
  L.push("| # | Product | Category | Match | Live ID | Packs (label: price; MRP) |", "|---|---|---|---|---|---|");
  r.products.forEach((p, i) => {
    const packsCell = p.packs
      .map((k) => `${k.label}: ₹${money(k.price)}${k.multiPack ? ` (2: ₹${money(packTotal(k.mrp, 2))}, 3: ₹${money(packTotal(k.mrp, 3))})` : ` (MRP ₹${money(k.mrp)} × ${k.pcs})`}${k.liveVariationId ? ` [var ${k.liveVariationId}]` : ""}`)
      .join("<br>");
    L.push(`| ${i + 1} | ${p.name} | ${p.category} | ${p.match}${p.convertToVariable ? ", simple→variable" : ""} | ${p.live?.id ?? "new"} | ${packsCell} |`);
  });
  L.push("");

  const notes = r.products.filter((p) => p.matchNote);
  L.push("## Naming and matching decisions", "", ...notes.map((p) => `- **${p.packs[0].rows[0].baseName}** → ${p.live ? `#${p.live.id} ${p.live.name}` : "new product"}: ${p.matchNote}`), "");

  L.push("## Live products not in the sheet (left unchanged)", "", ...r.unmatchedLive.map((p) => `- #${p.id} ${p.name} (${p.category})`), "");

  L.push("## Sheet rows → catalog", "", "| Line | Sheet item | MRP | Wt | Pcs | → Product | Pack | Price |", "|---|---|---|---|---|---|---|---|");
  for (const p of r.products) for (const k of p.packs) for (const row of k.rows) L.push(`| ${row.line} | ${row.itemName} | ${row.mrp} | ${row.weight} | ${row.pcs} | ${p.name}${p.live ? ` (#${p.live.id})` : " (new)"} | ${k.label} | ₹${money(k.price)} |`);
  for (const x of r.excludedRows) L.push(`| ${x.line} | ${x.itemName} | ${x.mrp} | ${x.weight} | ${x.pcs} | EXCLUDED | – | – |`);
  return L.join("\n") + "\n";
}

export async function runEcommPlan(opts: { out?: string; json?: string }) {
  const live = await fetchLive();
  const r = reconcile(loadItems(), live);
  const md = report(r);
  if (opts.out) writeFileSync(opts.out, md);
  if (opts.json) writeFileSync(opts.json, JSON.stringify(r, null, 1));
  console.log(md.split("\n## Excluded")[0]);
  if (r.problems.length) process.exitCode = 2;
  return r;
}
