import { existsSync, mkdirSync, writeFileSync } from "node:fs";
import { fileURLToPath } from "node:url";
import { wpGetAll } from "./http.ts";
import { mapCategory, mapProduct, type PlannedCategory, type PlannedProduct } from "./mapping.ts";
import { META } from "./meta-keys.ts";
import { loadReferenceCatalog, REFERENCE_PUBLIC_DIR } from "./reference.ts";

/**
 * READ-ONLY. Builds the import plan from the reference catalog and compares it with the live store
 * (GET requests only). Writes build/import-plan.json locally. Nothing on WordPress is changed.
 */

type Action = "create" | "update" | "unchanged-slug-exists";

interface LiveTerm {
  id: number;
  slug: string;
  name: string;
}
interface LiveProduct {
  id: number;
  slug: string;
  name: string;
  status: string;
  meta_data: { key: string; value: unknown }[];
}

export async function runPlan(opts: { offline: boolean }) {
  const ref = loadReferenceCatalog();
  const categories: PlannedCategory[] = ref.categories.map((c, i) => mapCategory(c, i, ref.products));
  const products: PlannedProduct[] = ref.products.map(mapProduct);

  // Every image the plan references must exist in the reference project.
  const images = [...categories.flatMap((c) => (c.coverImage ? [c.coverImage] : [])), ...products.flatMap((p) => p.images)];
  const missing = [...new Set(images.map((i) => i.file))].filter((f) => !existsSync(fileURLToPath(new URL(f, REFERENCE_PUBLIC_DIR))));

  let liveCategories: LiveTerm[] = [];
  let liveProducts: LiveProduct[] = [];
  if (!opts.offline) {
    liveCategories = await wpGetAll<LiveTerm>("/wc/v3/products/categories?_fields=id,slug,name");
    liveProducts = await wpGetAll<LiveProduct>("/wc/v3/products?status=any&_fields=id,slug,name,status,meta_data");
  }

  const categoryActions = categories.map((c) => ({
    slug: c.slug,
    action: (liveCategories.some((t) => t.slug === c.slug) ? "update" : "create") as Action,
  }));
  const productActions = products.map((p) => {
    const bySource = liveProducts.find((lp) => lp.meta_data?.some((m) => m.key === META.sourceId && m.value === p.sourceId));
    const bySlug = liveProducts.find((lp) => lp.slug === p.slug);
    const action: Action = bySource ? "update" : bySlug ? "unchanged-slug-exists" : "create";
    return { slug: p.slug, action, liveId: bySource?.id ?? bySlug?.id ?? null };
  });

  const count = (list: { action: Action }[], a: Action) => list.filter((x) => x.action === a).length;
  const uniqueImages = new Set(images.map((i) => i.file)).size;

  const summary = {
    source: "reference/nextjs/data (published catalog only; pending products and TEST prices excluded)",
    live: opts.offline ? "skipped (--offline)" : { categories: liveCategories.length, products: liveProducts.length },
    categories: { total: categories.length, create: count(categoryActions, "create"), update: count(categoryActions, "update") },
    products: {
      total: products.length,
      simple: products.filter((p) => p.type === "simple").length,
      variable: products.filter((p) => p.type === "variable").length,
      variations: products.reduce((n, p) => n + p.variations.length, 0),
      withPrice: products.filter((p) => p.regular_price || p.variations.some((v) => v.regular_price)).length,
      create: count(productActions, "create"),
      update: count(productActions, "update"),
      slugConflicts: count(productActions, "unchanged-slug-exists"),
    },
    images: { unique: uniqueImages, missing },
  };

  mkdirSync("build", { recursive: true });
  writeFileSync("build/import-plan.json", JSON.stringify({ summary, categoryActions, productActions, categories, products }, null, 2));

  console.log(JSON.stringify(summary, null, 2));
  console.log("\nFull plan written to build/import-plan.json (gitignored). No changes were made to WordPress.");
  if (missing.length) process.exitCode = 1;
}
