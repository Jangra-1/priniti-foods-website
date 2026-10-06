// Copies the Lucide icons used by the PHP templates into theme/priniti/assets/icons (committed, so the theme works
// without a build step for icons). Same icon set and version as lucide-react in reference/nextjs.
//
// Icons are found automatically from priniti_icon('name' / priniti_the_icon('name' calls in the theme, plus the
// names below that templates build dynamically (social links, homepage "why" items, order journey).
import { copyFileSync, mkdirSync, readdirSync, readFileSync, rmSync, statSync } from "node:fs";
import { createRequire } from "node:module";
import path from "node:path";

const dynamic = [
  "instagram", "facebook", "youtube", "twitter", // site config social links
  "wheat", "smile", "sparkles", "handshake", "layout-grid", // homepage "Why Priniti" icons
  "shopping-bag", "badge-check", "package", "truck", "package-check", "check", // order journey
  "info", "triangle-alert", // form notices
  "headphones", "phone", "globe", "mail", "map-pin", "user", // contact cards, account dashboard tiles
];

const found = new Set(dynamic);
const walk = (dir) => {
  for (const name of readdirSync(dir)) {
    const p = path.join(dir, name);
    if (statSync(p).isDirectory()) {
      if (!["assets", "node_modules"].includes(name)) walk(p);
    } else if (p.endsWith(".php")) {
      for (const m of readFileSync(p, "utf8").matchAll(/priniti_(?:the_)?icon\(\s*'([a-z0-9-]+)'/g)) found.add(m[1]);
    }
  }
};
walk("theme/priniti");

const require = createRequire(import.meta.url);
const src = path.join(path.dirname(require.resolve("lucide-static/package.json")), "icons");
const out = "theme/priniti/assets/icons";
rmSync(out, { recursive: true, force: true });
mkdirSync(out, { recursive: true });
const missing = [];
for (const name of [...found].sort()) {
  try {
    copyFileSync(path.join(src, `${name}.svg`), path.join(out, `${name}.svg`));
  } catch {
    missing.push(name);
  }
}
if (missing.length) {
  console.error(`icons: not found in lucide-static: ${missing.join(", ")}`);
  process.exit(1);
}
console.log(`icons: copied ${found.size} Lucide icons to ${out}`);
