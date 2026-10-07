import { mkdirSync, readFileSync, writeFileSync } from "node:fs";
import path from "node:path";
import { wpGetAll, wpSend } from "../http.ts";
import { META } from "../meta-keys.ts";

/**
 * Official product images (tools/import/data/official-images.json) for products that have none yet.
 *
 *   fetch  downloads every mapped URL to a local folder and checks it is a real PNG/JPEG of a sensible size, so the
 *          artwork can be inspected before anything is marked `verified`;
 *   apply  uploads each VERIFIED image to the WordPress media library, sets it as the product image (only on products
 *          that still have no image) and removes the internal "Image needed" note. Existing images are never replaced.
 */

export const IMAGE_MAP = path.resolve(import.meta.dirname, "../../data/official-images.json");

interface Entry {
  url: string | null;
  verified: boolean;
  note: string;
}
interface ImageMap {
  products: Record<string, Entry>;
}

export const loadImageMap = () => JSON.parse(readFileSync(IMAGE_MAP, "utf8")) as ImageMap;

/** Image type and pixel size from the file header (PNG IHDR / JPEG SOFn), or null when it is not a PNG or JPEG. */
export function imageInfo(bytes: Uint8Array): { type: "png" | "jpeg"; width: number; height: number } | null {
  const view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);
  if (bytes.length > 24 && bytes[0] === 0x89 && bytes[1] === 0x50 && bytes[2] === 0x4e && bytes[3] === 0x47) {
    return { type: "png", width: view.getUint32(16), height: view.getUint32(20) };
  }
  if (bytes.length > 4 && bytes[0] === 0xff && bytes[1] === 0xd8) {
    let i = 2;
    while (i + 9 < bytes.length) {
      if (bytes[i] !== 0xff) return null;
      const marker = bytes[i + 1];
      const len = view.getUint16(i + 2);
      if (marker >= 0xc0 && marker <= 0xcf && ![0xc4, 0xc8, 0xcc].includes(marker)) {
        return { type: "jpeg", height: view.getUint16(i + 5), width: view.getUint16(i + 7) };
      }
      i += 2 + len;
    }
  }
  return null;
}

const MIN_SIDE = 300;
const fileName = (product: string, url: string) => `priniti-${product.toLowerCase().replace(/[^a-z0-9]+/g, "-")}-official${path.extname(new URL(url).pathname).toLowerCase()}`;

export async function fetchImages(outDir: string) {
  mkdirSync(outDir, { recursive: true });
  const rows: string[] = [];
  for (const [product, e] of Object.entries(loadImageMap().products)) {
    if (!e.url) {
      rows.push(`${product}\tNO URL\t${e.note}`);
      continue;
    }
    try {
      const res = await fetch(e.url);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const bytes = new Uint8Array(await res.arrayBuffer());
      const info = imageInfo(bytes);
      if (!info) throw new Error("not a PNG or JPEG");
      if (Math.min(info.width, info.height) < MIN_SIDE) throw new Error(`too small (${info.width}×${info.height})`);
      writeFileSync(path.join(outDir, fileName(product, e.url)), bytes);
      rows.push(`${product}\tOK ${info.type} ${info.width}×${info.height} ${Math.round(bytes.length / 1024)} KB\t${e.url}`);
    } catch (err) {
      rows.push(`${product}\tFAILED ${(err as Error).message}\t${e.url}`);
    }
  }
  console.log(rows.join("\n"));
}

interface WcProduct {
  id: number;
  name: string;
  images: unknown[];
  meta_data: { key: string; value: unknown }[];
}

export async function applyImages(opts: { dryRun: boolean; dir: string }) {
  const map = loadImageMap().products;
  const products = await wpGetAll<WcProduct>("/wc/v3/products?status=any&context=edit");
  let done = 0;
  for (const [name, e] of Object.entries(map)) {
    const p = products.find((x) => x.name.toLowerCase() === name.toLowerCase());
    if (!p) {
      console.log(`${name}: product not found`);
      continue;
    }
    if (!e.url || !e.verified) {
      console.log(`${name}: skipped (${e.url ? "not verified" : "no official URL"})`);
      continue;
    }
    if (p.images.length) {
      console.log(`${name}: already has an image, left unchanged`);
      continue;
    }
    const file = fileName(name, e.url);
    const bytes = new Uint8Array(readFileSync(path.join(opts.dir, file)));
    const info = imageInfo(bytes);
    if (!info) throw new Error(`${name}: ${file} is not a valid image (run fetch again)`);
    console.log(`${opts.dryRun ? "[dry-run] " : ""}${name} (#${p.id}) ← ${e.url}`);
    if (opts.dryRun) continue;
    const media = await wpSend<{ id: number }>("POST", "/wp/v2/media", null, { raw: { bytes, contentType: info.type === "png" ? "image/png" : "image/jpeg", filename: file } });
    await wpSend("POST", `/wp/v2/media/${media.id}`, { alt_text: `${name} pack by Priniti Foods`, caption: "", description: `Official Priniti Foods product image (${e.url})` });
    const notes = p.meta_data.find((m) => m.key === META.internalNotes)?.value;
    const kept = (Array.isArray(notes) ? notes : []).filter((n) => !String(n).startsWith("Image needed"));
    await wpSend("PUT", `/wc/v3/products/${p.id}`, { images: [{ id: media.id }], meta_data: [{ key: META.internalNotes, value: kept }] });
    done++;
  }
  console.log(`${opts.dryRun ? "[dry-run] " : ""}images assigned: ${done}`);
}
