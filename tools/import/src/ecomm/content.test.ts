import assert from "node:assert/strict";
import { test } from "node:test";
import { contentPayload, loadContent } from "./content.ts";

test("content payload writes only content fields and escapes HTML", () => {
  const body = contentPayload({ source: "https://www.prinitifoods.com/x.php", officialTitle: "X", shortDescription: "A <crisp> snack.", description: "A <crisp> snack & more.", highlights: ["One"], ingredients: "Gram pulse", allergenNote: null, storage: "Store in a cool, dry place.", shelfLife: "Up to 6 months" });
  assert.deepEqual(Object.keys(body).sort(), ["description", "meta_data", "short_description"]);
  assert.equal(body.description, "<p>A &lt;crisp&gt; snack &amp; more.</p>");
  const meta = Object.fromEntries(body.meta_data.map((m) => [m.key, m.value]));
  assert.equal(meta._priniti_ingredients, "Gram pulse.");
  assert.equal(meta._priniti_storage, "Store in a cool, dry place. Shelf life: up to 6 months.");
});

test("official content never carries nutrition values or trade copy", () => {
  for (const [name, c] of Object.entries(loadContent())) {
    assert.ok(!("nutrition" in c), name);
    const text = [c.description, c.shortDescription, ...c.highlights].join(" ");
    assert.doesNotMatch(text, /distributor|retailer|wholesale|margin|offtake|replenish/i, name);
    assert.match(c.source, /^https:\/\/www\.prinitifoods\.com\//);
  }
});
