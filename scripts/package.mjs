// Assembles the deployable theme and plugin into build/ (what CI pushes to the deploy branches and what
// a manual ZIP upload would contain). Run `npm run build` first.
import { cpSync, existsSync, mkdirSync, rmSync } from "node:fs";
import path from "node:path";

const targets = [
  { src: "theme/priniti", out: "build/theme/priniti", exclude: ["assets/src"] },
  { src: "plugin/priniti-core", out: "build/plugin/priniti-core", exclude: [] },
];

if (!existsSync("theme/priniti/assets/dist/app.css")) {
  console.error("theme/priniti/assets/dist is missing: run `npm run build` first.");
  process.exit(1);
}

for (const t of targets) {
  rmSync(t.out, { recursive: true, force: true });
  mkdirSync(path.dirname(t.out), { recursive: true });
  cpSync(t.src, t.out, {
    recursive: true,
    filter: (p) => {
      const rel = path.relative(t.src, p);
      return !t.exclude.some((e) => rel === e || rel.startsWith(`${e}${path.sep}`));
    },
  });
  console.log(`packaged ${t.src} -> ${t.out}`);
}
