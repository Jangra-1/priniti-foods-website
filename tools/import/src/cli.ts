import { importConfig } from "./config.ts";
import { WpError } from "./http.ts";
import { runPlan } from "./plan.ts";

/**
 * Priniti catalog import CLI.
 *
 *   npm run import:plan                 read-only: build the plan and diff it against the live store
 *   npm run import:plan -- --offline    build the plan without contacting WordPress
 *
 * `apply` is intentionally not available yet: importing requires explicit approval (see README.md).
 */
const [command, ...flags] = process.argv.slice(2);

async function main() {
  switch (command) {
    case "plan":
      await runPlan({ offline: flags.includes("--offline") });
      return;
    case "apply":
      console.error(
        importConfig.writesAllowed && flags.includes("--apply")
          ? "apply is not implemented in this phase. Importing products requires explicit approval."
          : "Refusing to write: apply needs --apply AND PRINITI_IMPORT_ALLOW_WRITE=1, and is not enabled in this phase.",
      );
      process.exitCode = 1;
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
