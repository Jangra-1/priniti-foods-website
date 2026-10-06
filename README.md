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

The theme and plugin implement the whole design: homepage, shop, categories, product pages (gallery, pack sizes, quantity, add to cart, buy now), search, quick view, cart drawer and cart page (WooCommerce Store API), checkout (WooCommerce classic checkout in the design), order received, My Account, login and signup (email or mobile), track order (with Packed/Shipped/Delivered statuses), about, contact (stored + emailed enquiries), newsletter, and the four policy pages, responsive from phones to desktops.

Not done here (needs the live/staging site or business decisions): deployment and activation (see [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)), catalog import, WooCommerce settings, prices, GST, shipping rates, payment gateway.
