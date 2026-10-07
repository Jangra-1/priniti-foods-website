import { wpGetAll, wpSend } from "../http.ts";
import { META } from "../meta-keys.ts";
import { money } from "./model.ts";
import { fetchLive, loadItems } from "./plan.ts";
import { type PlannedEcommProduct, type PlannedPack, reconcile } from "./reconcile.ts";

/**
 * Writes the reconciled e-commerce catalog to WooCommerce:
 *   - prices: regular price = MRP (single pack) or MRP x Pcs (Pack of X); no sale price, no other discount;
 *   - pack data on every sellable product/variation (MRP per piece, Pcs, weight, sheet item reference);
 *   - multi-size products become variable products on the global "Pack size" attribute;
 *   - products missing from the catalog are created (no image: flagged in internal notes for replacement).
 * Never touches stock, tax class, shipping, payment settings or existing images. Idempotent: each run reconciles
 * against the live catalog first, so re-running only fills what is missing or different.
 */

const PACK_SOURCE = "ecomm-item-list";
/** Existing catalog names tidied for the storefront (slugs, and so URLs, are unchanged). */
export const RENAMES: Record<number, string> = { 90: "Noodles", 81: "A To Z" };

interface Attribute {
  id: number;
  slug: string;
}
interface Term {
  id: number;
  name: string;
}
interface Category {
  id: number;
  slug: string;
}
interface WcVariation {
  id: number;
  regular_price: string;
  sale_price: string;
  attributes: { id: number; option: string }[];
  meta_data: { key: string; value: unknown }[];
}
interface WcProduct {
  id: number;
  type: string;
  regular_price: string;
  sale_price: string;
  meta_data: { key: string; value: unknown }[];
}

const packMeta = (p: PlannedPack) => [
  { key: META.mrp, value: money(p.mrp) },
  { key: META.pcs, value: String(p.pcs) },
  { key: META.weight, value: p.weight },
  { key: META.ecommItem, value: p.rows[0].itemName },
  { key: META.packSource, value: PACK_SOURCE },
];

export async function runEcommApply(opts: { dryRun: boolean }) {
  const log = (s: string) => console.log((opts.dryRun ? "[dry-run] " : "") + s);
  const live = await fetchLive();
  const r = reconcile(loadItems(), live);
  if (r.problems.length) throw new Error(`Reconciliation has ${r.problems.length} problem(s); run ecomm:plan and resolve them first.`);

  const categories = await wpGetAll<Category>("/wc/v3/products/categories");
  const catId = (slug: string) => {
    const c = categories.find((x) => x.slug === slug);
    if (!c) throw new Error(`Category ${slug} not found`);
    return c.id;
  };
  const attribute = (await wpGetAll<Attribute>("/wc/v3/products/attributes")).find((a) => a.slug === "pa_pack-size");
  if (!attribute) throw new Error("Global attribute pa_pack-size not found");

  // 1. Attribute terms for every multi-size label.
  const terms = await wpGetAll<Term>(`/wc/v3/products/attributes/${attribute.id}/terms`);
  const needed = [...new Set(r.products.filter((p) => p.type === "variable").flatMap((p) => p.packs.map((k) => k.label)))];
  for (const label of needed.filter((l) => !terms.some((t) => t.name.toLowerCase() === l.toLowerCase()))) {
    log(`term + ${label}`);
    if (!opts.dryRun) terms.push(await wpSend<Term>("POST", `/wc/v3/products/attributes/${attribute.id}/terms`, { name: label }));
  }

  const attrPayload = (p: PlannedEcommProduct) => ({
    attributes: [{ id: attribute.id, variation: true, visible: true, options: p.packs.map((k) => k.label) }],
    default_attributes: [{ id: attribute.id, option: p.packs[0].label }],
  });

  let created = 0;
  let updated = 0;
  for (const p of r.products) {
    // 2. Product record.
    let productId = p.live?.id ?? 0;
    if (!p.live) {
      const body: Record<string, unknown> = {
        name: p.name,
        type: p.type,
        status: "publish",
        categories: [{ id: catId(p.category) }],
        meta_data: [{ key: META.internalNotes, value: ["Image needed: no official pack image was available when this product was created from the e-commerce item list."] }],
      };
      if (p.type === "simple") {
        Object.assign(body, { regular_price: money(p.packs[0].price), sale_price: "" });
        (body.meta_data as unknown[]).push(...packMeta(p.packs[0]), { key: META.packSize, value: p.packs[0].label });
      } else Object.assign(body, attrPayload(p));
      log(`create ${p.type} "${p.name}" in ${p.category}`);
      if (!opts.dryRun) productId = (await wpSend<{ id: number }>("POST", "/wc/v3/products", body)).id;
      created++;
    } else {
      const body: Record<string, unknown> = {};
      if (RENAMES[p.live.id] && RENAMES[p.live.id] !== p.live.name) body.name = RENAMES[p.live.id];
      if (p.type === "simple") {
        Object.assign(body, { regular_price: money(p.packs[0].price), sale_price: "", meta_data: [...packMeta(p.packs[0]), { key: META.packSize, value: p.packs[0].label }] });
      } else {
        Object.assign(body, attrPayload(p));
        if (p.convertToVariable) Object.assign(body, { type: "variable", regular_price: "", sale_price: "", meta_data: [{ key: META.packSize, value: "" }, { key: META.packSource, value: "" }] });
      }
      log(`update #${p.live.id} "${p.live.name}"${body.name ? ` → "${body.name}"` : ""}${p.convertToVariable ? " (simple → variable)" : ""}`);
      if (!opts.dryRun) await wpSend("PUT", `/wc/v3/products/${p.live.id}`, body);
      updated++;
    }

    // 3. Variations.
    if (p.type !== "variable") continue;
    const create = p.packs.filter((k) => !k.liveVariationId);
    const update = p.packs.filter((k) => k.liveVariationId);
    const variation = (k: PlannedPack, i: number) => ({
      regular_price: money(k.price),
      sale_price: "",
      status: "publish",
      menu_order: i,
      attributes: [{ id: attribute.id, option: k.label }],
      meta_data: packMeta(k),
    });
    const batch = {
      create: create.map((k) => variation(k, p.packs.indexOf(k))),
      update: update.map((k) => ({ id: k.liveVariationId, ...variation(k, p.packs.indexOf(k)) })),
    };
    log(`  variations: +${batch.create.length} ~${batch.update.length} (${p.packs.map((k) => `${k.label} ₹${money(k.price)}`).join(", ")})`);
    if (!opts.dryRun) await wpSend("POST", `/wc/v3/products/${productId}/variations/batch`, batch);
  }
  log(`products created: ${created}, updated: ${updated}`);
  if (!opts.dryRun) await verify();
}

/** Re-reads the live catalog and checks every sheet pack has the right price and pack data. */
export async function verify() {
  const live = await fetchLive();
  const r = reconcile(loadItems(), live);
  const errors: string[] = [...r.problems];
  let checked = 0;
  for (const p of r.products) {
    if (!p.live) {
      errors.push(`${p.name}: not found after apply`);
      continue;
    }
    if (p.type === "simple") {
      const { data } = { data: (await wpGetAll<WcProduct>(`/wc/v3/products?include=${p.live.id}&context=edit`))[0] };
      check(p.name, p.packs[0], data.regular_price, data.sale_price, data.meta_data);
      continue;
    }
    if (p.live.type !== "variable") errors.push(`${p.name}: expected variable, is ${p.live.type}`);
    const vars = await wpGetAll<WcVariation>(`/wc/v3/products/${p.live.id}/variations?context=edit`);
    for (const k of p.packs) {
      const v = vars.find((x) => x.attributes.some((a) => a.option.toLowerCase() === k.label.toLowerCase()));
      if (!v) errors.push(`${p.name} ${k.label}: variation missing`);
      else check(p.name, k, v.regular_price, v.sale_price, v.meta_data);
    }
    if (vars.length !== p.packs.length) errors.push(`${p.name}: ${vars.length} variations, expected ${p.packs.length}`);
  }
  function check(name: string, k: PlannedPack, regular: string, sale: string, meta: { key: string; value: unknown }[]) {
    checked++;
    const m = (key: string) => String(meta.find((x) => x.key === key)?.value ?? "");
    if (Number(regular) !== k.price) errors.push(`${name} ${k.label}: price ${regular}, expected ${money(k.price)}`);
    if (sale) errors.push(`${name} ${k.label}: unexpected sale price ${sale}`);
    if (Number(m(META.mrp)) !== k.mrp || Number(m(META.pcs)) !== k.pcs || m(META.weight) !== k.weight) errors.push(`${name} ${k.label}: pack meta mismatch`);
  }
  console.log(`verify: ${checked} packs checked, ${errors.length} error(s)`);
  for (const e of errors) console.log(`  - ${e}`);
  if (errors.length) process.exitCode = 3;
  return errors;
}
