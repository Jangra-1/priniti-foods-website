import { readFileSync } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { wpGetAll, wpSend } from "./http.ts";
import { mapCategory, mapProduct, type PlannedImage } from "./mapping.ts";
import { META, PACK_SIZE_ATTRIBUTE } from "./meta-keys.ts";
import { loadReferenceCatalog, REFERENCE_PUBLIC_DIR } from "./reference.ts";

/**
 * Writes the published reference catalog to WooCommerce. Idempotent: re-running updates instead of duplicating.
 *
 * - Media are matched by file slug, categories by slug, products by _priniti_source_id (then slug).
 * - Never sends a price, SKU or stock value the reference does not have (none exist yet -> "Price coming soon").
 * - Never deletes anything.
 * - Category term meta (Combos hide flag, origin, cover) needs the priniti-core plugin active; until then
 *   categories flagged hide-when-empty (Combos) are skipped so they cannot appear in navigation.
 */

interface Media { id: number; slug: string; source_url: string }
interface Term { id: number; slug: string; name: string }
interface Attr { id: number; slug: string }
interface Prod { id: number; slug: string; meta_data: { key: string; value: unknown }[] }

const log = (...a: unknown[]) => console.log(...a);

export async function runApply(opts: { status: "publish" | "draft"; termMeta: boolean }) {
  const ref = loadReferenceCatalog();
  const categories = ref.categories.map((c, i) => mapCategory(c, i, ref.products));
  const products = ref.products.map(mapProduct);

  // 1. Media
  const existingMedia = await wpGetAll<Media>("/wp/v2/media?_fields=id,slug,source_url");
  const mediaBySlug = new Map(existingMedia.map((m) => [m.slug, m]));
  const mediaId = new Map<string, number>();
  const ensureMedia = async (img: PlannedImage) => {
    if (mediaId.has(img.file)) return mediaId.get(img.file)!;
    const base = path.basename(img.file).replace(/\.[^.]+$/, "");
    const slug = `priniti-${base}`;
    let m = mediaBySlug.get(slug);
    if (!m) {
      const bytes = readFileSync(fileURLToPath(new URL(img.file, REFERENCE_PUBLIC_DIR)));
      m = await wpSend<Media>("POST", "/wp/v2/media", null, { raw: { bytes, contentType: "image/webp", filename: `priniti-${base}.webp` } });
      log("media +", base, m.id);
    }
    await wpSend("POST", `/wp/v2/media/${m.id}`, { slug, alt_text: img.alt, title: img.alt });
    mediaId.set(img.file, m.id);
    return m.id;
  };

  // 2. Global "Pack size" attribute
  const attrs = await wpGetAll<Attr>("/wc/v3/products/attributes");
  let packAttr = attrs.find((a) => a.slug === `pa_${PACK_SIZE_ATTRIBUTE.slug}`);
  if (!packAttr) {
    packAttr = await wpSend<Attr>("POST", "/wc/v3/products/attributes", { name: PACK_SIZE_ATTRIBUTE.name, slug: PACK_SIZE_ATTRIBUTE.slug, type: "select", order_by: "menu_order", has_archives: false });
    log("attribute + Pack size", packAttr.id);
  }
  const packTerms = await wpGetAll<Term>(`/wc/v3/products/attributes/${packAttr.id}/terms`);
  const neededTerms = [...new Set(products.flatMap((p) => p.attributes.flatMap((a) => a.options)))];
  for (const label of neededTerms) {
    if (!packTerms.some((t) => t.name === label)) {
      packTerms.push(await wpSend<Term>("POST", `/wc/v3/products/attributes/${packAttr.id}/terms`, { name: label }));
      log("pack size term +", label);
    }
  }

  // 3. Categories
  const liveCats = await wpGetAll<Term>("/wc/v3/products/categories?_fields=id,slug,name");
  const catId = new Map<string, number>();
  for (const c of categories) {
    const hidden = c.meta.priniti_hide_when_empty === "1";
    if (hidden && !opts.termMeta) {
      log("category skipped until priniti-core is active (hide-when-empty):", c.slug);
      continue;
    }
    const body = {
      name: c.name,
      slug: c.slug,
      menu_order: c.menu_order,
      ...(c.description ? { description: c.description } : {}),
      ...(c.coverImage ? { image: { id: await ensureMedia(c.coverImage) } } : {}),
    };
    const existing = liveCats.find((t) => t.slug === c.slug);
    const saved = existing
      ? await wpSend<Term>("PUT", `/wc/v3/products/categories/${existing.id}`, body)
      : await wpSend<Term>("POST", "/wc/v3/products/categories", body);
    catId.set(c.slug, saved.id);
    log(existing ? "category ~" : "category +", c.slug, saved.id);
    if (opts.termMeta) await wpSend("POST", `/wp/v2/product_cat/${saved.id}`, { meta: c.meta });
  }

  // 4. Products
  const liveProducts = await wpGetAll<Prod>("/wc/v3/products?status=any&_fields=id,slug,meta_data");
  for (const p of products) {
    const cat = catId.get(p.categorySlug);
    if (!cat) {
      log("product skipped (category not created):", p.slug);
      continue;
    }
    const images = [];
    for (const img of p.images) images.push({ id: await ensureMedia(img) });
    const body = {
      name: p.name,
      slug: p.slug,
      type: p.type,
      status: opts.status,
      catalog_visibility: "visible",
      categories: [{ id: cat }],
      images,
      featured: p.featured,
      ...(p.description ? { description: p.description } : {}),
      ...(p.regular_price ? { regular_price: p.regular_price } : {}),
      ...(p.sale_price ? { sale_price: p.sale_price } : {}),
      ...(p.sku ? { sku: p.sku } : {}),
      ...(p.stock_status ? { stock_status: p.stock_status } : {}),
      attributes: p.attributes.map((a) => ({ id: packAttr!.id, variation: a.variation, visible: a.visible, options: a.options })),
      ...(p.type === "variable" ? { default_attributes: [] } : {}),
      meta_data: p.meta_data,
    };
    const existing =
      liveProducts.find((lp) => lp.meta_data?.some((m) => m.key === META.sourceId && m.value === p.sourceId)) ?? liveProducts.find((lp) => lp.slug === p.slug);
    const saved = existing
      ? await wpSend<Prod>("PUT", `/wc/v3/products/${existing.id}`, body)
      : await wpSend<Prod>("POST", "/wc/v3/products", body);
    log(existing ? "product ~" : "product +", p.slug, saved.id, p.type);

    if (p.type === "variable") {
      const liveVars = await wpGetAll<{ id: number; attributes: { name: string; option: string }[] }>(`/wc/v3/products/${saved.id}/variations?_fields=id,attributes`);
      const create = [];
      const update = [];
      for (const v of p.variations) {
        const body = {
          attributes: [{ id: packAttr.id, option: v.packSize }],
          ...(v.regular_price ? { regular_price: v.regular_price } : {}),
          ...(v.sale_price ? { sale_price: v.sale_price } : {}),
          ...(v.sku ? { sku: v.sku } : {}),
          ...(v.stock_status ? { stock_status: v.stock_status } : {}),
          meta_data: v.meta_data,
        };
        const match = liveVars.find((lv) => lv.attributes.some((a) => a.option === v.packSize));
        if (match) update.push({ id: match.id, ...body });
        else create.push(body);
      }
      await wpSend("POST", `/wc/v3/products/${saved.id}/variations/batch`, { create, update });
      log("  variations", `+${create.length} ~${update.length}`);
    }
  }
  log("apply finished. Nothing was deleted.");
}
