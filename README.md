# Priniti Foods website

The Priniti Foods storefront on **WordPress + WooCommerce** (shop.prinitifoods.com): a custom theme that reproduces the approved Next.js design exactly, with WooCommerce as the backend for products, categories, cart, checkout, orders and payments.

| Path | What it is |
|---|---|
| `reference/nextjs/` | The uploaded Next.js project, copied **verbatim**. It is the visual and UX source of truth and is never deployed. |
| `theme/priniti/` | The WordPress theme. PHP templates mirror the reference components; Tailwind CSS v4 uses the same design tokens; small React islands handle overlays and cart. |
| `plugin/priniti-core/` | Store data and business logic (product fields, pack sizes, category flags). |
| `tools/import/` | Catalog import tool: maps `reference/nextjs/data` to WooCommerce. Read-only `plan` only for now. |
| `tools/dev/` | Local-preview helpers (never deployed). |
| `scripts/` | Build, packaging and lint scripts. |
| `docs/` | [Architecture and component map](docs/ARCHITECTURE.md), [deployment](docs/DEPLOYMENT.md). |

## Develop

```bash
npm ci
npm run build        # icons, fonts, CSS (Tailwind) and JS islands (Vite) -> theme/priniti/assets/dist
npm run verify       # typecheck + php -l + build
npm run package      # build/theme/priniti and build/plugin/priniti-core, exactly what gets deployed
npm run import:plan  # READ-ONLY catalog plan against the live store (add `-- --offline` to skip WordPress)
```

`theme/priniti/assets/dist` is not committed: CI builds it and publishes ready-to-run deploy branches (see [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)).

## Credentials

No credentials are stored in this repository, ever. In the Claude cloud environment the egress proxy authenticates requests to shop.prinitifoods.com. Elsewhere, put a WordPress Application Password in a gitignored `.env` (see `.env.example`).

## Status

Phase 1 (this commit): repository setup, reference copy, theme shell (header, desktop/mobile navigation, categories menu, search dialog, cart drawer on the WooCommerce Store API, toasts, footer, 404, generic page), plugin structure, import-tool structure, CI and deploy branches. Nothing on the live site has been changed.
