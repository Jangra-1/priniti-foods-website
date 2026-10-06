// Copies the self-hosted font files used by theme/priniti/assets/src/css/fonts.css into assets/dist/fonts.
import { copyFileSync, mkdirSync } from "node:fs";
import { createRequire } from "node:module";
import path from "node:path";

const require = createRequire(import.meta.url);
const out = "theme/priniti/assets/dist/fonts";
mkdirSync(out, { recursive: true });

const fonts = {
  "@fontsource/inter": [400, 500, 600, 700, 800].map((w) => `inter-latin-${w}-normal.woff2`),
  "@fontsource/poppins": [500, 600, 700, 800].map((w) => `poppins-latin-${w}-normal.woff2`),
};

for (const [pkg, files] of Object.entries(fonts)) {
  const dir = path.join(path.dirname(require.resolve(`${pkg}/package.json`)), "files");
  for (const f of files) copyFileSync(path.join(dir, f), path.join(out, f));
}
console.log(`fonts: copied ${Object.values(fonts).flat().length} files to ${out}`);
