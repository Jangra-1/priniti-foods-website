// Syntax-checks every PHP file in the theme and plugin with `php -l`.
import { execFileSync } from "node:child_process";
import { readdirSync, statSync } from "node:fs";
import path from "node:path";

const roots = ["theme/priniti", "plugin/priniti-core"];
const skip = new Set(["node_modules", "dist", "vendor"]);
const files = [];
const walk = (dir) => {
  for (const name of readdirSync(dir)) {
    const p = path.join(dir, name);
    if (statSync(p).isDirectory()) {
      if (!skip.has(name)) walk(p);
    } else if (p.endsWith(".php")) files.push(p);
  }
};
roots.forEach(walk);

let failed = 0;
for (const f of files) {
  try {
    execFileSync("php", ["-l", f], { stdio: "pipe" });
  } catch (e) {
    failed++;
    process.stderr.write(e.stdout?.toString() || e.stderr?.toString() || `${f}: lint failed\n`);
  }
}
console.log(`php -l: ${files.length - failed}/${files.length} files OK`);
process.exit(failed ? 1 : 0);
