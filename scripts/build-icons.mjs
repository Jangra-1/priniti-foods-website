// Copies the Lucide icons used by PHP templates into theme/priniti/assets/icons.
// Same icon set and version as lucide-react in reference/nextjs, so PHP-rendered icons match the design exactly.
// Add a name here when a template starts using a new icon, then run `npm run build:icons` and commit the SVG.
import { copyFileSync, mkdirSync } from "node:fs";
import { createRequire } from "node:module";
import path from "node:path";

export const icons = [
  "arrow-right",
  "chevron-down",
  "chevron-right",
  "facebook",
  "image",
  "instagram",
  "menu",
  "package",
  "search",
  "shopping-bag",
  "twitter",
  "user",
  "x",
  "youtube",
];

const require = createRequire(import.meta.url);
const src = path.join(path.dirname(require.resolve("lucide-static/package.json")), "icons");
const out = "theme/priniti/assets/icons";
mkdirSync(out, { recursive: true });
for (const name of icons) copyFileSync(path.join(src, `${name}.svg`), path.join(out, `${name}.svg`));
console.log(`icons: copied ${icons.length} Lucide icons to ${out}`);
