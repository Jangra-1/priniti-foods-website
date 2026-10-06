# Priniti Foods — storefront frontend (Batches 1–3, Phases 1–3)

Next.js 16 · React 19 · TypeScript · Tailwind CSS v4 · Zustand · Lucide

```bash
npm install
npm run dev        # http://localhost:3000
npm run typecheck
```

## Where things live
- `data/` all mock data and site config (swap for real data here only)
- `lib/api/` async accessors the UI calls; replace bodies with backend fetches later
- `app/globals.css` design tokens (`@theme`); colours are PROVISIONAL until brand assets arrive
- `data/site.ts` set `logo` and `fssaiLicense` when supplied
- `components/ui` primitives, `components/layout` shell, `components/commerce` shopping components
- `components/sections` homepage sections, `components/catalog` shop/category UI
- `data/home.ts` homepage copy (draft), `data/reviews.ts` SAMPLE reviews

## Adding real images (no code changes)
Save files in `public/` and they are picked up at build/request time (webp, png, jpg):
- products: `public/images/products/<product-slug>-1.webp` (then `-2`, `-3`, `-4`)
- categories: `public/images/categories/<category-slug>.webp`
- homepage: `public/images/brand/hero.webp` and `public/images/brand/story.webp`

Shop/category filters live in the URL: `?category=&price=&rating=&stock=1&sort=&q=&page=`.

Sample data is flagged `isMock: true`; the UI shows a "Sample" badge for it. The demo is set to noindex in `app/layout.tsx`; remove at launch.

## Catalog (Batch 3, Phase 1)
- `data/products.ts`: 57 PUBLISHED products (confirmed by old website structure + supplied pack image). Price, MRP, SKU, ingredients, nutrition, descriptions and ratings are PENDING (left undefined, never invented). Pack-size variants exist only where verified (`source` says how).
- `data/pending-products.ts`: 29 known products NOT published, each with a reason and an open question.
- `data/categories.ts`: 10 official categories. Combos is unconfirmed and intentionally absent.
- Images: `public/images/products/<slug>-<n>.webp` are web-ready copies of the supplied packs (colour-converted/downscaled only). Rebuild with `python3 scripts/prepare-images.py "<unzipped Products Images folder>"`. Unresolved pack images live in `assets/pending-images/` (not served).
- Logo: `public/logo/priniti-logo.svg|png`, converted from the original CorelDRAW file.
- Filters/sorts without data (price, rating, availability, newest) are hidden automatically and appear once the data exists.

## Phase 2: pages and commerce (checkout is NOT live)
- Routes: `/product/[slug]` (57 published products), `/search?q=`, `/cart`, `/checkout` (preview only), plus Batch 2's `/`, `/shop`, `/category/[slug]`.
- A product can only be added to the cart when its selected pack size has a real `price`. No product has one yet, so every card/page shows "Price coming soon" and the cart stays empty. Add `price`/`mrp` (and `inStock`) to a variant in `data/products.ts` (or via the API layer) and the cart, Buy Now, price filters and price sorts switch on by themselves.
- Checkout lists what is still missing: pricing, shipping rules, GST, payment gateway, order backend, confirmation email/WhatsApp (`data/integrations.ts`). Shipping, tax and the final total show "Pending"; nothing is calculated, stored or sent.
- `data/pending-products.ts` is never imported by the storefront. `npm run check:catalog` fails if that changes, if a published product gains an unsupplied field, or if an image is missing.
- Cart/wishlist storage keys are `priniti-cart-v2` / `priniti-wishlist-v2` so old demo carts are ignored.
- The announcement bar and the free-shipping line are hidden until `siteConfig.announcement` and `siteConfig.commerce.freeShippingThreshold` are set to confirmed values.

## Verify locally
```bash
npm install
npm run check:catalog
npm run typecheck
npm run build
npm run dev   # http://localhost:3000
```

## Phase 3: homepage, branding, test prices, combos
- **Test prices** (`data/test-prices.ts`, switched by `NEXT_PUBLIC_TEST_PRICES` in `.env.local`; see `.env.example`): development placeholders only. They are flagged `isTestPrice`, never set an MRP, show a TEST PRICE label and a site-wide banner, and are excluded from structured data. Products with no verified pack size get a "Pack size TBC" test variant so the cart can be exercised. To remove them: set the switch to `off` (or delete `data/test-prices.ts` and its import in `lib/api/products.ts`). Cart lines saved with test prices are discarded automatically when the switch is off.
- **Homepage merchandising** (`data/merchandising.ts`): hand-curated slug lists with neutral labels ("Curated picks", "Explore the range", "Sweets & bakery"). They switch to "Best Sellers", "Popular Products" and "New Arrivals" automatically only if real `bestseller`/`new` badges or review counts exist in the data. Every slug must be published (`check:catalog` enforces it).
- **Combos** (`data/combos.ts`): ecommerce-only category, intentionally empty and unpublished. `/category/combos` shows "coming soon" and is noindex; it appears in navigation and category lists only once a real combo product is added to `data/combos.ts`.
- **Reviews and social**: no sample reviews are shown (empty state until real reviews exist); social links render only for URLs set in `data/site.ts`.

## Figma visual redesign (visual layer only)
Restyled to the supplied homepage design: header (pill search), hero with a round stage of real packs, category cards, tabbed Featured Products on the existing ProductCard, dark "Why" band, product carousel, combo banner, sweets/cookies tiles, stats band (live catalog counts), story, newsletter, social strip and a dark footer.
`data/`, `lib/`, `store/`, `hooks/`, `types/` and `scripts/` are unchanged from Phase 3. Claims in the design (delivery promises, certifications, years, customer counts, ratings, reviews, prices, mobile app, stock photos) were NOT reproduced because the data does not exist.

## Phase 4: core pages
`/about`, `/contact`, `/login`, `/signup`, `/track-order`, built on the existing design system.
- Company facts live only in `data/company.ts` (verified against the official Priniti Foods website). Do not add unverified facts there.
- Login, signup, contact and track-order are FRONTEND-ONLY previews: format validation and an honest "not connected" message. No network calls, no storage, no sessions, no stored passwords.
- Known dead links that predate this phase: `/shipping-policy`, `/return-policy`, `/privacy-policy`, `/terms` (no pages yet).

### /about (premium refinement)
Real company photographs live in `public/images/about/` (cropped from the official About Us page screenshot supplied by the business; replace them with the original high-resolution files at the same filenames when available). All facts and milestones are in `data/company.ts`. Valued-partner logos are intentionally not shown until verified logo files are supplied.

### /contact (premium refinement)
Hero, three contact cards, form + "Reach Priniti Foods" panel, two location cards (map-search links on the verified addresses, no embedded map), and a closing CTA. Contact details come only from `data/company.ts`. The form is a frontend-only preview: it validates and shows an honest "not connected" message; nothing is sent or stored.

### /login, /signup, /track-order (final UI)
Frontend-only. Each validates input and shows an honest "will be connected in the next integration phase" message after a valid submit: no sessions, no stored passwords or credentials, no network calls, no redirects, no fake loading. The track-order stages are illustrative only (never marked complete). The Terms & Conditions and Privacy Policy mentions on signup are disabled "coming soon" text until those pages exist.

## Phase 5: legal and policy pages
`/privacy-policy`, `/terms`, `/shipping-policy`, `/return-policy`. Content lives in `data/legal.ts` (DRAFT, for business and legal review). Business-specific rules that are not configured yet (charges, timelines, return windows, payment methods, retention, jurisdiction) are shown as "To be confirmed before ecommerce launch" and must be filled in, with legal review, before launch. Contact details come from `data/company.ts`. The footer already linked to these four routes.

## Phase 6A: checkout foundation (development, TEST prices)
- `/checkout` shows items (variant, quantity, unit price, line total), subtotal, shipping "To be calculated", tax "To be confirmed before ecommerce launch" and total "Not final". "Payment integration coming next" is a disabled button. No order is created, nothing is stored (not even in localStorage) or sent.
- Typed models: `types/checkout.ts` (form data) and `types/order.ts` (Order, DraftOrder, PaymentStatus, OrderStatus). `lib/order.ts` builds an in-memory DRAFT (no id, no timestamp, never persisted).
- Cart and checkout flag lines whose price no longer matches the catalog (`getPriceIndex()` in `lib/api/products.ts`). Test prices stay in `data/test-prices.ts` behind `NEXT_PUBLIC_TEST_PRICES`; replacing them with real prices is a data change only.
