/**
 * E-commerce item list (tools/import/data/ecomm-item-list.csv, exported from "Ecomm Item List.xlsx", sheet 30-09-2025):
 * parsing, product-name normalisation, live-catalog matching and pack pricing. Pure functions only (no I/O),
 * so the reconciliation is deterministic and unit-testable.
 */

export interface ItemRow {
  line: number; // CSV line (2 = first item)
  section: string;
  itemName: string; // raw, including the internal nomenclature
  mrp: number; // per-piece MRP from the sheet
  weight: string; // "50 g", "1 kg" (CURRENT WT column)
  weightGrams: number;
  pcs: number;
  baseName: string; // cleaned, e.g. "Potato Chips Classic Salt"
}

export interface LiveProduct {
  id: number;
  name: string;
  slug: string;
  type: string;
  status: string;
  category: string; // first product_cat slug
  imageCount: number;
  packSize: string; // _priniti_pack_size (simple products)
  variations: { id: number; label: string }[];
}

/* -------------------------------------------------------------------------
 * Parsing
 * ---------------------------------------------------------------------- */

export function parseCsv(text: string): string[][] {
  const rows: string[][] = [];
  let row: string[] = [];
  let cell = "";
  let quoted = false;
  for (let i = 0; i < text.length; i++) {
    const ch = text[i];
    if (quoted) {
      if (ch === '"' && text[i + 1] === '"') {
        cell += '"';
        i++;
      } else if (ch === '"') quoted = false;
      else cell += ch;
    } else if (ch === '"') quoted = true;
    else if (ch === ",") {
      row.push(cell);
      cell = "";
    } else if (ch === "\n" || ch === "\r") {
      if (ch === "\r" && text[i + 1] === "\n") i++;
      row.push(cell);
      if (row.some((c) => c !== "")) rows.push(row);
      row = [];
      cell = "";
    } else cell += ch;
  }
  if (cell !== "" || row.length) {
    row.push(cell);
    rows.push(row);
  }
  return rows;
}

/** "50G" -> { label: "50 g", grams: 50 }; "1KG" -> { label: "1 kg", grams: 1000 }. */
export function parseWeight(raw: string): { label: string; grams: number } {
  const m = /^\s*([\d.]+)\s*(KG|G)\s*$/i.exec(raw);
  if (!m) throw new Error(`Unrecognised weight "${raw}"`);
  const value = Number(m[1]);
  const kg = m[2].toUpperCase() === "KG";
  return { label: `${m[1]} ${kg ? "kg" : "g"}`, grams: kg ? value * 1000 : value };
}

/**
 * Removes the internal nomenclature ("Rs30(6L*10P*50G)", "(60P*200G)") and splits run-together words
 * ("PotatoChips ClassicSalt" -> "Potato Chips Classic Salt", "DonutCake" -> "Donut Cake").
 */
export function cleanName(itemName: string): string {
  const base = itemName.replace(/\s*(Rs\s*\d+)?\s*\(.*$/i, "").trim();
  return base
    .replace(/([a-z])([A-Z])/g, "$1 $2")
    .replace(/\s+/g, " ")
    .trim();
}

export function parseItems(csv: string): ItemRow[] {
  const [header, ...rows] = parseCsv(csv);
  const col = (n: string) => header.indexOf(n);
  return rows.map((r, i) => {
    const w = parseWeight(r[col("current_wt")]);
    return {
      line: i + 2,
      section: r[col("section")],
      itemName: r[col("item_name")],
      mrp: Number(r[col("mrp")]),
      weight: w.label,
      weightGrams: w.grams,
      pcs: Number(r[col("pcs")]),
      baseName: cleanName(r[col("item_name")]),
    };
  });
}

/* -------------------------------------------------------------------------
 * Normalisation and matching
 * ---------------------------------------------------------------------- */

/** Word-level synonyms seen between the sheet and the catalog. */
const SYNONYMS: Record<string, string> = { salted: "salt", noodles: "noodle", chips: "chip" };
/** Words that carry no product identity ("Cream 'n' Onion", "Noodles (Yellow)" colour note, "Boondi Plain"). */
const FILLER = new Set(["n", "and", "the", "plain"]);

/**
 * Normalised token set: lower case, punctuation and parenthetical notes removed, doubled letters collapsed
 * ("Panchrattan" = "Panchratan"), trailing plural "s" removed, synonyms applied, word order ignored.
 */
export function tokens(name: string): string[] {
  const words = name
    .toLowerCase()
    .replace(/\([^)]*\)/g, " ")
    .replace(/['’`]/g, "")
    .replace(/[^a-z0-9]+/g, " ")
    .split(" ")
    .filter(Boolean)
    .map((w) => SYNONYMS[w] ?? w)
    .map((w) => w.replace(/(.)\1+/g, "$1"))
    .map((w) => (w.length > 3 && w.endsWith("s") ? w.slice(0, -1) : w))
    .map((w) => SYNONYMS[w] ?? w)
    .filter((w) => !FILLER.has(w));
  return [...new Set(words)].sort();
}

export const productKey = (name: string) => tokens(name).join(" ");

export function similarity(a: string, b: string): number {
  const x = new Set(tokens(a));
  const y = new Set(tokens(b));
  const inter = [...x].filter((t) => y.has(t)).length;
  return inter / new Set([...x, ...y]).size;
}

/** Category a sheet row belongs to (existing Priniti category slugs). */
export function categoryFor(row: ItemRow): string {
  const n = row.baseName.toLowerCase();
  switch (row.section) {
    case "SNACK DIVISION":
      if (n.startsWith("potato chips")) return "potato-chips";
      if (n.startsWith("charchare")) return "charchare-sticks";
      if (n.startsWith("popcorn")) return "popcorn";
      if (n.startsWith("ringo star")) return "ringo-star-rings";
      return "puffs-fryums";
    case "NAMKEEN TINY":
    case "NAMKEEN FAMILY":
      return "indian-traditional-namkeen";
    case "RUSK":
      return "rusk";
    case "COOKIES":
      return n.includes("donut") ? "donut-cakes" : "cookies";
    case "SWEETS":
      return "sweets";
    default:
      throw new Error(`Unknown section "${row.section}" (line ${row.line})`);
  }
}

/** Customer-facing names for products that are new to the catalog (clean spelling of the sheet's base names). */
export const NEW_PRODUCT_NAMES: Record<string, string> = {
  "Potato Chips Spicy Masti": "Potato Chips Spicy Masti",
  "Chiji Noodles": "Chiji Noodles",
  "Roll N Roll": "Roll N Roll",
  "Manchurian Fried Rice": "Manchurian Fried Rice",
  "Mintoze Baby Ring": "Mintoze Baby Ring",
  "Tomato Katori": "Tomato Katori",
  Loopyz: "Loopyz",
  Puffcorn: "Puffcorn",
  "Veg Biryani": "Veg Biryani",
  "Chilli Storm": "Chilli Storm",
  "Puff Hot Spicy": "Puff Hot Spicy",
  Bhujia: "Bhujia",
  "Boondi Masala": "Boondi Masala",
  "Cocktail Mix": "Cocktail Mix",
  Cornflakes: "Cornflakes",
  "Cornflakes Mixture": "Cornflakes Mixture",
  "Jhatpat Bhel": "Jhatpat Bhel",
  "Salted Peanuts": "Salted Peanuts",
  "Choco Vanilla Donut Cake": "Choco Vanilla Donut Cake",
  "Strawberry Vanilla Donut Cake": "Strawberry Vanilla Donut Cake",
};

/** Explicitly excluded from the e-commerce catalog by the owner. */
export const EXCLUDED = [productKey("Potato Chips Sizzling Hot")];

/**
 * Reviewed decisions for near-misses (similar names that are or are not the same product). Each decision was made
 * from the pack artwork in the catalog, not from the name alone, and is printed in the reconciliation report.
 */
export const REVIEWED: Record<string, { match: string | null; reason: string }> = {
  [productKey("Noodle")]: { match: "noodles-yellow", reason: "\"Noodles (Yellow)\" pack is the fried snack noodle sold at Rs5/Rs10; \"Masala Noodles\" is instant noodles with a seasoning sachet, not in the sheet." },
  [productKey("Boondi Plain")]: { match: "boondi", reason: "Catalog \"Boondi\" artwork is plain boondi (\"fried balls of gram pulse flour\")." },
  [productKey("Boondi Masala")]: { match: null, reason: "Masala boondi is a different flavour from the plain boondi in the catalog." },
  [productKey("Bhujia")]: { match: null, reason: "Bhujia (plain) is listed separately from Aloo Bhujia in the sheet, with different MRPs." },
  [productKey("Chiji Noodles")]: { match: null, reason: "Different product from Noodles (Yellow) and Masala Noodles." },
  [productKey("Puff Hot Spicy")]: { match: null, reason: "Different flavour from the catalog's Puff Tangy Tomato." },
  [productKey("Cornflakes")]: { match: null, reason: "Sheet lists \"Cornflakes\" (Rs5) and \"Cornflakes Mixture\" (200 g) under different names; kept separate (see report)." },
  [productKey("Cornflakes Mixture")]: { match: null, reason: "Not in the catalog; kept separate from \"Cornflakes\" because the sheet names them differently." },
};

/* -------------------------------------------------------------------------
 * Pricing
 * ---------------------------------------------------------------------- */

/** Discount on 2- and 3-pack purchases of single packs (Pcs = 1). Mirrors plugin/priniti-core/includes/pack-pricing.php. */
export const MULTI_PACK_DISCOUNT = 0.12;
export const MULTI_PACK_MAX = 3;

const round2 = (n: number) => Math.round(n * 100) / 100;

/** Price of one sellable unit: one pack for Pcs = 1, the whole Pack of X for Pcs > 1. */
export const unitPrice = (mrp: number, pcs: number) => round2(mrp * pcs);

/** Line total for `packs` packs of a Pcs = 1 item: MRP for one, MRP x n x 0.88 for two or three. */
export function packTotal(mrp: number, packs: number): number {
  if (packs < 1 || packs > MULTI_PACK_MAX) throw new Error(`packs must be 1-${MULTI_PACK_MAX}`);
  return round2(packs === 1 ? mrp : mrp * packs * (1 - MULTI_PACK_DISCOUNT));
}

/** Variation / pack label shown to customers. */
export const packLabel = (row: Pick<ItemRow, "pcs" | "weight">) => (row.pcs > 1 ? `Pack of ${row.pcs} × ${row.weight}` : row.weight);

export const money = (n: number) => (Number.isInteger(n) ? String(n) : n.toFixed(2));
