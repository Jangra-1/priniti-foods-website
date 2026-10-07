import assert from "node:assert/strict";
import { existsSync, readFileSync } from "node:fs";
import path from "node:path";
import { test } from "node:test";

const ROOT = path.resolve(import.meta.dirname, "../..");
const DIR = path.join(ROOT, "theme/priniti/assets/images/banners");
const config = JSON.parse(readFileSync(path.join(ROOT, "tools/banners/banners.json"), "utf8")).banners as { id: string; kind: string; category: string; products: string[] }[];
const manifest = JSON.parse(readFileSync(path.join(DIR, "banners.json"), "utf8")) as Record<
  string,
  { kind: string; category: string; products: { slug: string; categories: string[]; image: string }[]; files: Record<string, { file: string; width: number; height: number; bytes: number }> }
>;

const CATEGORIES = ["indian-traditional-namkeen", "potato-chips", "charchare-sticks", "popcorn", "puffs-fryums", "ringo-star-rings", "rusk", "sweets", "cookies", "donut-cakes"];

test("three homepage banners and one banner for every category", () => {
  assert.equal(config.filter((b) => b.kind === "home").length, 3);
  assert.deepEqual(config.filter((b) => b.kind === "category").map((b) => b.category).sort(), [...CATEGORIES].sort());
  for (const c of CATEGORIES) assert.ok(manifest[`category-${c}`], `missing banner for ${c}`);
});

test("every banner shows only products of its own category (as verified against the store when built)", () => {
  for (const b of config) {
    const m = manifest[b.id];
    assert.ok(m, `${b.id} was not built`);
    assert.deepEqual(m.products.map((p) => p.slug), b.products, `${b.id}: built products differ from the config`);
    for (const p of m.products) assert.ok(p.categories.includes(b.category), `${b.id}: ${p.slug} is not in ${b.category}`);
    assert.ok(b.products.length >= 1 && b.products.length <= 5, `${b.id}: 1 to 5 products`);
  }
});

test("pack images are the store's real product images", () => {
  for (const m of Object.values(manifest)) for (const p of m.products) assert.match(p.image, /^https:\/\/shop\.prinitifoods\.com\/wp-content\/uploads\//);
});

test("desktop 1600x700 and mobile 1000x700 WebP files exist and stay small", () => {
  for (const [id, m] of Object.entries(manifest)) {
    assert.deepEqual([m.files.desktop.width, m.files.desktop.height], [1600, 700], id);
    assert.deepEqual([m.files.mobile.width, m.files.mobile.height], [1000, 700], id);
    for (const f of Object.values(m.files)) {
      assert.ok(existsSync(path.join(DIR, f.file)), f.file);
      assert.match(f.file, /\.webp$/);
      assert.ok(f.bytes < 200 * 1024, `${f.file} is ${f.bytes} bytes`);
    }
  }
});

test("lifestyle people (when added) are approved, licensed cut-outs that leave the products dominant", () => {
  const full = JSON.parse(readFileSync(path.join(ROOT, "tools/banners/banners.json"), "utf8")).banners as {
    id: string;
    products: string[];
    front?: string[];
    person: null | { file: string; source: string; license: string; mobile?: boolean };
  }[];
  for (const b of full) {
    if (!b.person) continue;
    assert.ok(b.person.source?.trim() && b.person.license?.trim(), `${b.id}: person needs a source and a licence`);
    assert.match(b.person.file, /^tools\/banners\/people\/[^/]+\.png$/, `${b.id}: person file must be a PNG in tools/banners/people/`);
    assert.ok(existsSync(path.join(ROOT, b.person.file)), `${b.id}: ${b.person.file} is missing`);
    const mainRow = b.products.filter((s) => !(b.front ?? []).includes(s));
    assert.ok(mainRow.length <= 3, `${b.id}: at most 3 main-row packs with a person`);
    assert.deepEqual((manifest[b.id] as unknown as { person?: { file: string } }).person?.file, b.person.file, `${b.id}: rebuild the banners after adding a person`);
  }
});
