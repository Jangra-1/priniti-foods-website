import assert from "node:assert/strict";
import { test } from "node:test";
import { cleanName, packLabel, packTotal, parseWeight, productKey, unitPrice } from "./model.ts";
import { loadItems } from "./plan.ts";

test("owner's pricing examples: single packs (Pcs = 1)", () => {
  const cases: [number, number, number][] = [
    [20, 35.2, 52.8],
    [30, 52.8, 79.2],
    [55, 96.8, 145.2],
    [85, 149.6, 224.4],
    [100, 176, 264],
    [300, 528, 792],
  ];
  for (const [mrp, two, three] of cases) {
    assert.equal(packTotal(mrp, 1), mrp);
    assert.equal(packTotal(mrp, 2), two);
    assert.equal(packTotal(mrp, 3), three);
  }
  assert.throws(() => packTotal(30, 4));
});

test("owner's pricing examples: Pack of X (Pcs > 1)", () => {
  assert.equal(unitPrice(5, 14), 70);
  assert.equal(unitPrice(5, 12), 60);
  assert.equal(unitPrice(10, 10), 100);
  assert.equal(unitPrice(10, 24), 240);
});

test("nomenclature is stripped from names", () => {
  assert.equal(cleanName("PotatoChips ClassicSalt Rs30(6L*10P*50G)"), "Potato Chips Classic Salt");
  assert.equal(cleanName("All in One Rs10(20L*10P*34G)"), "All in One");
  assert.equal(cleanName("Choco Vanilla DonutCake(6B*10P*45G)"), "Choco Vanilla Donut Cake");
  assert.equal(cleanName("Soan Papdi (12P*900G)"), "Soan Papdi");
});

test("normalised matching handles spelling, punctuation, plurals and word order", () => {
  assert.equal(productKey("Potato Chips Cream Onion"), productKey("Potato Chips Cream 'n' Onion"));
  assert.equal(productKey("Panchratan"), productKey("Panchrattan"));
  assert.equal(productKey("Noodle"), productKey("Noodles (Yellow)"));
  assert.equal(productKey("Potato Chips Classic Salt"), productKey("Potato Chips Classic Salted"));
  assert.equal(productKey("Charchare Mast Masala"), productKey("CharChare Mast Masala"));
  assert.notEqual(productKey("Bhujia"), productKey("Aloo Bhujia"));
  assert.notEqual(productKey("Chiji Noodles"), productKey("Noodles (Yellow)"));
});

test("labels and weights", () => {
  assert.deepEqual(parseWeight("1KG"), { label: "1 kg", grams: 1000 });
  assert.equal(packLabel({ pcs: 1, weight: "50 g" }), "50 g");
  assert.equal(packLabel({ pcs: 14, weight: "13 g" }), "Pack of 14 × 13 g");
});

test("the item list parses completely", () => {
  const items = loadItems();
  assert.equal(items.length, 162);
  assert.ok(items.every((r) => r.mrp > 0 && r.pcs >= 1 && r.weightGrams > 0 && !/Rs\d|\(|\*/.test(r.baseName)));
});
