# Catalog import tool

Maps the published catalog in `reference/nextjs/data` to WooCommerce and compares it with the live store.

```bash
npm run import:plan               # READ-ONLY: GET requests only; writes build/import-plan.json locally
npm run import:plan -- --offline  # no network at all
```

- `apply` writes the catalog (owner-approved for the live site): `PRINITI_IMPORT_ALLOW_WRITE=1 npm run import:apply -- --apply` (`--draft` to keep products unpublished, `--term-meta` once priniti-core is active to write category flags and create Combos). It never deletes anything and never sends a price, SKU or stock value the reference does not have.
- Imports only published products (`data/products.ts`, `data/combos.ts`). Never `data/pending-products.ts`, never development TEST prices.
- Idempotent by design: products are matched by `_priniti_source_id` (e.g. `prn-aloo-bhujia`), then by slug.
- Mapping rules are in `src/mapping.ts`: 0–1 verified pack size = simple product, 2+ = variable product on the global "Pack size" attribute; MRP = regular price, selling price = sale price; products start as drafts; prices are sent only when real.
- Credentials: none in the repo. In the Claude cloud environment the proxy authenticates (the npm script sets `NODE_USE_ENV_PROXY=1` so Node's fetch uses it). Elsewhere: `WP_APP_USER` / `WP_APP_PASSWORD` in a gitignored `.env`. Errors print only the HTTP status and the WordPress error code/message.

`apply` uploads the 59 product images, creates the global "Pack size" attribute, the categories (order, cover image) and the 57 products (6 variable with 12 variations), all without prices ("Price coming soon").
