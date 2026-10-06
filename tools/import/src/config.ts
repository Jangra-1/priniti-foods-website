/**
 * Import tool configuration. Credentials are NEVER read from files in the repository and never printed.
 *
 * - In the Claude cloud environment, the egress proxy authenticates requests to shop.prinitifoods.com, so no
 *   credentials are needed here at all.
 * - Elsewhere, set WP_APP_USER and WP_APP_PASSWORD (a WordPress Application Password) in the shell or in a
 *   gitignored .env file.
 */
export const importConfig = {
  baseUrl: (process.env.WP_BASE_URL ?? "https://shop.prinitifoods.com").replace(/\/+$/, ""),
  /** Writes need BOTH this env flag and the --apply CLI flag (and are not enabled in this phase). */
  writesAllowed: process.env.PRINITI_IMPORT_ALLOW_WRITE === "1",
};

export function authHeader(): Record<string, string> {
  const user = process.env.WP_APP_USER;
  const pass = process.env.WP_APP_PASSWORD;
  if (!user || !pass) return {};
  return { Authorization: `Basic ${Buffer.from(`${user}:${pass}`).toString("base64")}` };
}
