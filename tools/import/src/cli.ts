import { importConfig } from "./config.ts";
import { WpError } from "./http.ts";
import { runApply } from "./apply.ts";
import { runPlan } from "./plan.ts";

/**
 * Priniti catalog import CLI.
 *
 *   npm run import:plan                 read-only: build the plan and diff it against the live store
 *   npm run import:plan -- --offline    build the plan without contacting WordPress
 *
 *   npm run import:apply -- --apply [--draft] [--term-meta]
 *     writes the catalog (owner-approved for the live site). Requires --apply AND PRINITI_IMPORT_ALLOW_WRITE=1.
 *     --term-meta also writes category flags (needs the priniti-core plugin active).
 */
const [command, ...flags] = process.argv.slice(2);

async function main() {
  switch (command) {
    case "plan":
      await runPlan({ offline: flags.includes("--offline") });
      return;
    case "apply":
      if (!importConfig.writesAllowed || !flags.includes("--apply")) {
        console.error("Refusing to write: apply needs --apply AND PRINITI_IMPORT_ALLOW_WRITE=1.");
        process.exitCode = 1;
        return;
      }
      await runApply({ status: flags.includes("--draft") ? "draft" : "publish", termMeta: flags.includes("--term-meta") });
      return;
    default:
      console.error("Usage: tsx tools/import/src/cli.ts plan [--offline]");
      process.exitCode = 1;
  }
}

main().catch((e) => {
  // Only the status and WordPress error code/message are printed (never headers or credentials).
  console.error(e instanceof WpError ? e.message : e instanceof Error ? e.message : String(e));
  process.exitCode = 1;
});
