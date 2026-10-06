import { existsSync } from "node:fs";
import path from "node:path";

const EXTENSIONS = ["webp", "png", "jpg", "jpeg"];

/**
 * Server-only. Looks for a file in /public without its extension, e.g.
 * findPublicImage("images/products/sample-rusk-1") finds public/images/products/sample-rusk-1.webp.
 * Lets real packshots be added by dropping files in place, with no component or data edits.
 */
export function findPublicImage(basePath: string): string | undefined {
  for (const ext of EXTENSIONS) {
    const rel = `${basePath}.${ext}`;
    if (existsSync(path.join(process.cwd(), "public", rel))) return `/${rel}`;
  }
  return undefined;
}
