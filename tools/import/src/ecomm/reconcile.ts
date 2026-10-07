import { categoryFor, EXCLUDED, type ItemRow, type LiveProduct, money, NEW_PRODUCT_NAMES, packLabel, productKey, REVIEWED, similarity, unitPrice } from "./model.ts";

export interface PlannedPack {
  label: string; // "50 g" | "Pack of 10 × 28 g"
  weight: string;
  pcs: number;
  mrp: number; // per piece, from the sheet
  price: number; // selling price of one unit (MRP x Pcs)
  multiPack: boolean; // Pcs = 1: eligible for the 1/2/3 pack selector
  rows: ItemRow[];
  liveVariationId: number | null; // existing variation with the same label
}

export interface PlannedEcommProduct {
  key: string;
  name: string;
  category: string;
  live: LiveProduct | null;
  match: "exact" | "normalised" | "reviewed" | "new";
  matchNote: string;
  type: "simple" | "variable";
  convertToVariable: boolean;
  packs: PlannedPack[];
}

export interface Reconciliation {
  totalRows: number;
  excludedRows: ItemRow[];
  products: PlannedEcommProduct[];
  unmatchedLive: LiveProduct[];
  problems: string[];
}

const slugOf = (p: LiveProduct) => p.slug;

export function reconcile(rows: ItemRow[], live: LiveProduct[]): Reconciliation {
  const problems: string[] = [];
  const excludedRows = rows.filter((r) => EXCLUDED.includes(productKey(r.baseName)));
  const groups = new Map<string, ItemRow[]>();
  for (const r of rows) {
    if (excludedRows.includes(r)) continue;
    const key = productKey(r.baseName);
    groups.set(key, [...(groups.get(key) ?? []), r]);
  }

  const liveByKey = new Map<string, LiveProduct[]>();
  for (const p of live) liveByKey.set(productKey(p.name), [...(liveByKey.get(productKey(p.name)) ?? []), p]);
  const used = new Set<number>();
  const products: PlannedEcommProduct[] = [];

  for (const [key, group] of groups) {
    const first = group[0];
    const category = categoryFor(first);
    if (group.some((r) => categoryFor(r) !== category)) problems.push(`${first.baseName}: rows fall in different categories`);

    let liveMatch: LiveProduct | null = null;
    let match: PlannedEcommProduct["match"] = "new";
    let matchNote = "";
    const reviewed = REVIEWED[key];
    const candidates = liveByKey.get(key) ?? [];

    if (reviewed && !reviewed.match && candidates.length === 1) {
      // Reviewed as a new product, and that product now exists (created by an earlier apply): match it by its exact name.
      liveMatch = candidates[0];
      match = "exact";
      matchNote = reviewed.reason;
    } else if (reviewed) {
      match = reviewed.match ? "reviewed" : "new";
      matchNote = reviewed.reason;
      if (reviewed.match) {
        liveMatch = live.find((p) => slugOf(p) === reviewed.match) ?? null;
        if (!liveMatch) problems.push(`${first.baseName}: reviewed match "${reviewed.match}" not found in the live catalog`);
      }
    } else if (candidates.length === 1) {
      liveMatch = candidates[0];
      match = liveMatch.name.trim().toLowerCase() === first.baseName.toLowerCase() ? "exact" : "normalised";
      matchNote = match === "normalised" ? `"${first.baseName}" = "${liveMatch.name}"` : "";
    } else if (candidates.length > 1) {
      problems.push(`${first.baseName}: ${candidates.length} live products share the normalised name (${candidates.map((c) => c.id).join(", ")})`);
    } else {
      // No exact normalised match: any similar live name must have a reviewed decision, never a silent guess.
      const near = live.filter((p) => similarity(p.name, first.baseName) >= 0.5);
      if (near.length) problems.push(`${first.baseName}: unreviewed near-miss with ${near.map((p) => `"${p.name}"`).join(", ")}`);
      if (!NEW_PRODUCT_NAMES[first.baseName]) problems.push(`${first.baseName}: new product without a reviewed customer-facing name`);
    }
    if (liveMatch) {
      if (used.has(liveMatch.id)) problems.push(`${first.baseName}: live product ${liveMatch.id} matched twice`);
      used.add(liveMatch.id);
      if (liveMatch.category !== category) problems.push(`${first.baseName}: live category ${liveMatch.category} differs from ${category}`);
    }

    // One pack per (weight, pcs). Two sheet rows for the same pack would be a conflict.
    const packs = new Map<string, PlannedPack>();
    for (const r of group) {
      const label = packLabel(r);
      const existing = packs.get(label);
      if (existing) {
        problems.push(`${first.baseName}: two rows for "${label}" (lines ${existing.rows[0].line} and ${r.line})`);
        continue;
      }
      packs.set(label, {
        label,
        weight: r.weight,
        pcs: r.pcs,
        mrp: r.mrp,
        price: unitPrice(r.mrp, r.pcs),
        multiPack: r.pcs === 1,
        rows: [r],
        liveVariationId: liveMatch?.variations.find((v) => v.label.toLowerCase() === label.toLowerCase())?.id ?? null,
      });
    }
    // Single packs first (lightest to heaviest), then multipacks.
    const ordered = [...packs.values()].sort((a, b) => Number(a.pcs > 1) - Number(b.pcs > 1) || a.rows[0].weightGrams - b.rows[0].weightGrams || a.price - b.price);
    const type = ordered.length > 1 ? "variable" : "simple";
    if (liveMatch?.type === "variable" && type === "simple") problems.push(`${first.baseName}: live product is variable but the sheet has one pack`);

    products.push({
      key,
      name: liveMatch ? liveMatch.name : (NEW_PRODUCT_NAMES[first.baseName] ?? first.baseName),
      category,
      live: liveMatch,
      match,
      matchNote,
      type,
      convertToVariable: !!liveMatch && liveMatch.type === "simple" && type === "variable",
      packs: ordered,
    });
    for (const p of ordered) if (!(p.price > 0)) problems.push(`${first.baseName} ${p.label}: price ${money(p.price)}`);
  }

  return { totalRows: rows.length, excludedRows, products, unmatchedLive: live.filter((p) => !used.has(p.id)), problems };
}
