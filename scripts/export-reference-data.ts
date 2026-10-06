/**
 * Exports the reference content (reference/nextjs/data) to PHP arrays for the theme, so copy stays word for word.
 * Run `npm run export:data` after changing the reference data, and commit the output (theme/priniti/inc/data).
 */
import { writeFileSync } from "node:fs";
import { company } from "../reference/nextjs/data/company.ts";
import { homeContent } from "../reference/nextjs/data/home.ts";
import { policyMeta, privacyPolicy, returnPolicy, shippingPolicy, termsPolicy } from "../reference/nextjs/data/legal.ts";
import { merchandising } from "../reference/nextjs/data/merchandising.ts";
import { pendingIntegrations } from "../reference/nextjs/data/integrations.ts";

function php(value: unknown, indent = 1): string {
  const pad = "\t".repeat(indent);
  const close = "\t".repeat(indent - 1);
  if (value === null || value === undefined) return "null";
  if (typeof value === "string") return `'${value.replace(/\\/g, "\\\\").replace(/'/g, "\\'")}'`;
  if (typeof value === "number" || typeof value === "boolean") return String(value);
  if (Array.isArray(value)) {
    if (!value.length) return "array()";
    return `array(\n${value.map((v) => `${pad}${php(v, indent + 1)},`).join("\n")}\n${close})`;
  }
  const entries = Object.entries(value as Record<string, unknown>).filter(([, v]) => v !== undefined && typeof v !== "function");
  if (!entries.length) return "array()";
  return `array(\n${entries.map(([k, v]) => `${pad}'${k}' => ${php(v, indent + 1)},`).join("\n")}\n${close})`;
}

function write(name: string, source: string, value: unknown) {
  const out = `<?php
/**
 * GENERATED from reference/nextjs/${source} by scripts/export-reference-data.ts. Do not edit by hand:
 * change the reference, then run \`npm run export:data\`.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

return ${php(value)};
`;
  writeFileSync(`theme/priniti/inc/data/${name}.php`, out);
  console.log(`wrote theme/priniti/inc/data/${name}.php`);
}

// Brand images are optional files in the reference (none supplied yet); the theme resolves them itself.
const { hero, brandStory, ...homeRest } = homeContent;
const { image: _h, ...heroRest } = hero;
const { image: _s, ...storyRest } = brandStory;

write("company", "data/company.ts", company);
write("home", "data/home.ts", { ...homeRest, hero: heroRest, brandStory: storyRest });
write("legal", "data/legal.ts", {
  meta: policyMeta,
  policies: { "privacy-policy": privacyPolicy, terms: termsPolicy, "shipping-policy": shippingPolicy, "return-policy": returnPolicy },
});
write("merchandising", "data/merchandising.ts", merchandising);
write("integrations", "data/integrations.ts", pendingIntegrations);
