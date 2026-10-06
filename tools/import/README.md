# Catalog import tool

Maps the published catalog in `reference/nextjs/data` to WooCommerce and compares it with the live store.

```bash
npm run import:plan               # READ-ONLY: GET requests only; writes build/import-plan.json locally
npm run import:plan -- --offline  # no network at all
```

- **Read-only in this phase.** `apply` refuses to run. Writing will need `--apply`, `PRINITI_IMPORT_ALLOW_WRITE=1` and explicit approval, and will run against staging first.
- Imports only published products (`data/products.ts`, `data/combos.ts`). Never `data/pending-products.ts`, never development TEST prices.
- Idempotent by design: products are matched by `_priniti_source_id` (e.g. `prn-aloo-bhujia`), then by slug.
- Mapping rules are in `src/mapping.ts`: 0–1 verified pack size = simple product, 2+ = variable product on the global "Pack size" attribute; MRP = regular price, selling price = sale price; products start as drafts; prices are sent only when real.
- Credentials: none in the repo. In the Claude cloud environment the proxy authenticates (the npm script sets `NODE_USE_ENV_PROXY=1` so Node's fetch uses it). Elsewhere: `WP_APP_USER` / `WP_APP_PASSWORD` in a gitignored `.env`. Errors print only the HTTP status and the WordPress error code/message.

Planned for the import phase (after approval): media upload (59 product images), categories with order/cover images and flags, products and variations, then a verification pass.
