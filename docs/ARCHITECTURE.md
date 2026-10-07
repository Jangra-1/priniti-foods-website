# Architecture

## Principles

1. `reference/nextjs` is the source of truth. Every template names the reference component it ports, keeps its Tailwind classes and its copy, and hides anything whose data does not exist (never invents content).
2. WooCommerce owns commerce data: products, categories, prices, stock, cart, checkout, orders, payments, emails.
3. PHP renders every page (SEO, no layout shift). React islands only add the interactive overlays and widgets the reference implements as client components.
4. The plugin holds data and business rules; the theme holds presentation. Switching themes must not lose data.

## Styling

- `theme/priniti/assets/src/css/app.css` contains the reference `@theme` tokens, keyframes and base layer verbatim, plus a tiny WordPress-only block (admin-bar offset).
- Tailwind CSS v4 scans only the theme's PHP templates and island sources (`@source`), so the output contains exactly the classes the theme uses.
- Fonts: Inter (body) and Poppins (display), self-hosted from `@fontsource` (same weights as `next/font` in the reference).
- Icons: Lucide, same version as the reference (`lucide-static` SVGs for PHP, `lucide-react` in islands).
- The reference uses `tailwind-merge` to resolve conflicting classes. In PHP, recipes such as `priniti_button_classes()` and `priniti_icon_button_classes()` take the conflicting part (size, variant) as an argument instead.

## Interactivity (islands)

One bundle (`assets/src/js/app.tsx`, built by Vite) mounts the global overlays into `#priniti-overlays` and wires the server-rendered header:

| Reference | Theme |
|---|---|
| `store/ui.ts`, `store/toast.ts`, `store/search.ts` | `assets/src/js/stores/*` (same Zustand stores) |
| `store/cart.ts` (localStorage cart) | `stores/cart.ts`, backed by the **WooCommerce Store API** (`/wp-json/wc/store/v1/cart`). "Save for later" stays in the browser (`priniti-saved-v1`) because WooCommerce has no equivalent. |
| `useScrolled`, DesktopNav `CategoriesMenu` | `header.ts` (vanilla JS on the PHP header) |
| `ui/Dialog`, `ui/Drawer`, `ui/IconButton`, `ui/Button`, `ui/Toaster` | `components/*` (ported; `next/link` becomes `<a>`) |

Server data reaches the islands through `window.PRINITI` (`inc/assets.php`: URLs, navigation, categories, Store API root and nonce, commerce settings). Search suggestions are fetched lazily from `GET /wp-json/priniti/v1/search-index` the first time the search dialog opens.

`window.priniti` exposes `openCart()`, `toast()` and `addToCart()` for later page islands (product cards, product page).

## Component map

Every reference component has a counterpart. ✅ ported · ➖ replaced by WooCommerce or intentionally not ported

| Reference | Theme / plugin |
|---|---|
| `app/layout.tsx`, `layout/*` (Header, DesktopNav, MobileNavigation, SearchModal, Footer, Logo, AnnouncementBar, PageHeader, Breadcrumbs) | `header.php`, `footer.php`, `template-parts/layout/*`, islands `MobileNavigation`, `SearchModal` ✅ |
| `ui/*` (Button, IconButton, Badge, Eyebrow, SectionHeading, Input, Select, Textarea, Dialog, Drawer, Modal, Toaster) | `inc/components/ui.php` + islands ✅ |
| `app/page.tsx`, `sections/*` (Hero, PackFan, CategorySection, FeaturedProducts, WhyPriniti, carousel, PromoBanner, PromoTiles, FeaturedProduct, ReviewsSection, StatsBand, BrandStory, NewsletterSignup, SocialGrid) | `front-page.php`, `inc/components/sections.php` ✅ |
| `app/shop`, `app/category/[slug]`, `app/search`, `catalog/*` (CatalogView, CatalogToolbar, FilterSidebar, FilterFields, Pagination, CategoryBanner, CategoryCard) | `templates/shop.php`, `category.php`, `search.php`, `inc/components/catalog.php`, `assets/src/js/catalog.ts` ✅ |
| `commerce/*` (ProductCard, ProductGrid, ProductCarousel, PriceDisplay, RatingStars, ProductImage, WishlistButton, QuickViewButton, AddToCartButton, ReviewCard) | `inc/components/commerce.php`, `assets/src/js/interactions.ts` ✅ |
| `app/product/[slug]`, ProductGallery, ProductPurchasePanel, PackSizeSelector, QuantitySelector, ProductDetails | `templates/product.php` + `interactions.ts` ✅ |
| QuickViewModal, CartDrawer, CartItem, CartView, OrderSummary | islands on the WooCommerce Store API ✅ |
| `app/checkout`, CheckoutView, CheckoutForm, IntegrationStatus | WooCommerce classic checkout with `woocommerce/checkout/*` overrides; readiness panel computed from live settings ✅ |
| `lib/order.ts`, `types/order.ts`, `store/cart.ts` (localStorage cart) | ➖ WooCommerce cart and orders |
| `app/login`, `app/signup`, AuthShell, LoginForm, SignupForm, PasswordField, FormNotice | `templates/login.php`, `signup.php` + priniti-core accounts ✅ |
| My Account (no reference page) | `templates/account.php`, `woocommerce/myaccount/*` in the design's style ✅ |
| `app/track-order`, TrackOrderForm, OrderJourney, TrackHelp | `templates/track-order.php` + priniti-core tracking ✅ |
| `app/about`, JourneyTimeline | `templates/about.php` ✅ |
| `app/contact`, ContactCards, ReachPanel, LocationCards, ContactForm | `templates/contact.php` + priniti-core enquiries ✅ |
| `app/{privacy-policy,terms,shipping-policy,return-policy}`, PolicyPage | `templates/policy.php` ✅ (now at the Phase 5 URLs below, plus `/cookie-policy`) |
| `app/not-found.tsx` | `404.php` ✅ |
| Order received (no reference page) | `woocommerce/checkout/thankyou.php` built from the design's components ✅ |
| TestPriceBanner, TestPriceBadge, `data/test-prices.ts` | ➖ WooCommerce prices are real |
| `components/home/*` | ➖ unused duplicates of `components/sections/*` |

### Storefront extensions (beyond the reference, v0.3.0)

Same tokens and visual language as the reference; these extend it rather than replace it.

| Piece | Where |
| --- | --- |
| Decorative vectors (`priniti_decor()` blob, dots, ring, wave, sparkle, leaf, grain, squiggle; `priniti_decor_backdrop()`) | `inc/components/decor.php` |
| Empty / coming-soon state (`priniti_empty_state()`), `tint` badge tone, `.skeleton` shimmer, `[data-reveal]` scroll reveal | `inc/components/ui.php`, `assets/src/css/app.css`, `interactions.ts` |
| Homepage: decorated hero with real count badges, "Snacks for Every Mood" (`priniti_section_moods`), "Shop On The Go" (`priniti_section_on_the_go`) | `inc/components/sections.php`, `front-page.php` |
| Shop hero (`priniti_shop_hero`), category pills, branded category hero with pack fan, related categories, snack-box promo strip | `inc/components/commerce.php`, `templates/shop.php`, `templates/category.php` |
| Product page: decorated gallery with hover zoom, pack-size cards, assurances (facts only) and delivery placeholder, info grid and accordions, reviews block with empty state, "More from", "Explore the range", sticky mobile buy bar | `templates/product.php`, `inc/components/commerce.php`, `interactions.ts` |
| Header category menu with thumbnails; footer with newsletter/social band and Policies column | `template-parts/layout/desktop-nav.php`, `footer.php`, `inc/config.php` |

| Campaign pieces (v0.4.0): promo strip (`priniti_promo_strip`, copy via `priniti_promo_strip_messages` filter), sample testimonials (`priniti_sample_reviews` filter; real WooCommerce reviews replace them automatically), Instagram feed (`priniti_section_instagram`) | `inc/components/campaign.php` |
| Category art (eyebrow, fallback copy, accents per category; `priniti_category_art` filter), pack stage (`priniti_pack_stage`), multi-view product gallery (`priniti_product_gallery`) | `inc/components/commerce.php`, `inc/components/decor.php`, `interactions.ts` |

| Homepage hero slider (v0.6.0, banner images v0.7.0): exactly three slides from `priniti_hero_slides()` (filterable): Namkeen, Potato Chips, Cookies. Each slide's banner shows real packs of that category only; "Starting at" from their live prices, Shop + Explore Products CTAs | `inc/components/campaign.php` (`priniti_section_hero_slider`), `interactions.ts` (`initHeroSlider`) |
| Category showcase (v0.6.0): curated products per category for the category hero (`priniti_category_showcase` filter); category images fall back to the first showcase product when the term has no thumbnail (`priniti_category_with_image`) | `inc/catalog.php`, `templates/category.php` |
| Header logo in a white rounded container (official SVG, aspect ratio kept); compact footer whose link groups collapse on phones (`initFooter`) | `template-parts/layout/header.php`, `logo.php`, `footer.php`, `interactions.ts` |

**Banners (v0.7.0).** The homepage hero slides and every category hero use banner images built by
`tools/banners/build-banners.py` (`npm run banners`) from `tools/banners/banners.json`, written to
`assets/images/banners/` (WebP: `<id>-desktop.webp` 1600 x 700, `<id>-mobile.webp` 1000 x 700, plus `banners.json`).
- Packs are the real official product images, fetched from the public Store API and only trimmed, scaled (aspect kept),
  slightly tilted and shadowed. Backgrounds, the red stage and the floating ingredients are drawn in code. No text is
  baked in: headings, copy and CTAs are HTML.
- The builder refuses a product that is not in the banner's WooCommerce category; `npm test` checks the manifest
  (3 homepage banners, one per category, category-only products, sizes, file sizes).
- `person` in banners.json is an optional transparent PNG layer for an approved lifestyle image (none yet).
- Below 1024 px the HTML text sits above the 1000 x 700 image (on the image's own top colour); from 1024 px the text
  overlays the calm left side of the 1600 x 700 image. Width/height attributes and fixed aspect ratios prevent CLS.

**Hero slider.** All slides share one grid cell, so the hero never changes height. Slide 1 is server-rendered visible
with a high-priority image and works without JavaScript; slides 2 and 3 keep their image URLs (`<source>` and `<img>`)
in `data-hero-*` until the page has loaded. Autoplay every 3 s (`data-interval`), cross-fade, pause on hover, keyboard
focus and hidden tabs, no autoplay with reduced motion; dots, previous/next and swipe. Inactive slides are `inert` and
`aria-hidden`.

Catalog pages show 12 products (`PRINITI_PAGE_SIZE`), so 2-, 3- and 4-column grids end on full rows.

**Instagram feed.** Uses the official Instagram API (graph.instagram.com `/me/media`) with a long-lived token for the brand's
professional account. Add `define( 'PRINITI_INSTAGRAM_TOKEN', '…' );` to `wp-config.php` on the server (never commit it) and set the
Instagram profile URL in `inc/config.php` (`social`). Posts are cached for an hour. Tokens last 60 days; refresh with
`https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=…`. Until a token exists the section shows a
labelled "Feed coming soon" placeholder (no fake posts).

**Promo strip.** Display only: it does not create a coupon. Before launch either set up a matching WooCommerce coupon or change/remove the
copy with `add_filter( 'priniti_promo_strip_messages', '__return_empty_array' );`.

**Product gallery.** Shows every real product photo. When a product has fewer than three, it adds views made from the first official
photo (a labelled close-up crop and the pack on a Priniti backdrop); these are labelled on the page and in their alt text.

Data rules: these components only display existing product data. Missing ingredients, nutrition, storage and highlights read
"Information coming soon"; ratings and reviews appear only when real WooCommerce reviews exist; unpriced products show "Price coming soon"
and an enquiry link instead of a cart button.

## E-commerce pricing (Ecomm Item List)

The master list is `tools/import/data/ecomm-item-list.csv`, a CSV snapshot of "Ecomm Item List.xlsx" (sheet 30-09-2025).
`docs/ECOMM-RECONCILIATION.md` maps every sheet row to its WooCommerce product and pack.

- **Model.** One WooCommerce product per sheet product; each (weight, Pcs) row is a pack. Products with one pack are simple,
  others are variable on the global *Pack size* attribute (`50 g`, `Pack of 14 × 13 g`). Every sellable product/variation
  stores `_priniti_mrp` (MRP of one piece), `_priniti_pcs`, `_priniti_weight` and `_priniti_ecomm_item` (sheet item, internal).
- **Prices.** Regular price = MRP for single packs (Pcs = 1), MRP × Pcs for a Pack of X. No sale prices.
- **1 / 2 / 3 packs.** Single packs are bought as 1, 2 or 3 packs: the product page and quick view set the cart quantity,
  and priniti-core (`includes/pack-pricing.php`) prices 2 or 3 packs at MRP × n × 0.88 in the cart, so cart, checkout and
  orders charge what the page shows. Lines of single packs are capped at 3; Pack-of-X lines keep the site limit (10) and
  are never discounted.
- **Tooling.** `npm run ecomm:plan` (read-only reconciliation, writes a report), `npm run ecomm:apply` (dry run),
  `PRINITI_IMPORT_ALLOW_WRITE=1 npm run ecomm:apply -- --apply` (write), `npm run ecomm:apply -- --verify` (check live
  prices and pack data). Matching uses normalised names (case, punctuation, plurals, doubled letters, word order,
  synonyms) and reviewed decisions for near-misses, never silent fuzzy guesses. `npm test` covers the pricing examples.
- **Local preview.** `npx tsx tools/dev/export-catalog.ts --ecomm` builds the local catalog from the same reconciliation;
  the local cart mock calls priniti-core's own pricing function.

## Official product information

`tools/import/data/official-content.json` holds product copy taken only from the official product pages on www.prinitifoods.com
(built by `tools/import/scripts/build-official-content.py` from saved pages; run it with `python3 -I`). Per product: short and full
description (consumer copy only; trade and distributor wording is dropped), up to three highlights (from the page's FAQ answers),
key ingredients as described on the page, allergen note where the page gives one, storage and shelf life, and the source URL.
The official site publishes no nutrition values, so nutrition reads "Information coming soon" everywhere; so do fields of
products whose official page is missing or unusable (listed in the PR). `npm run ecomm:content` is a dry run;
`PRINITI_IMPORT_ALLOW_WRITE=1 npm run ecomm:content -- --apply` writes description, short description, highlights, ingredients,
storage and `_priniti_content_source` only (never prices, variations, images or categories).

The product page reads: title, gallery, pack size and 1/2/3 packs, Add to cart / Buy now, assurances and delivery, description and
highlights, then accordions (Ingredients open; Nutrition, Pack sizes, Storage, Shipping & Delivery), related products, reviews.

## Content and data

- `inc/data/*.php` are generated from `reference/nextjs/data` (`npm run export:data`; CI fails if they drift): company facts, homepage copy, merchandising slugs, legal policies, checkout integration labels.
- Catalog data comes from WooCommerce through `inc/catalog.php`, shaped like the reference `Product` type. Filters, sorts, search and capabilities are ported line for line from `lib/catalog.ts` / `lib/api/products.ts`.
- Class conflicts are resolved by `priniti_cx()`, a small tailwind-merge equivalent, so component overrides behave like the reference's `cn()`.

## priniti-core

| Module | What it does |
|---|---|
| `meta-keys.php`, `product-fields.php`, `category-fields.php`, `admin.php` | The design's product fields (highlights, ingredients, nutrition, storage, shipping note, FAQs, pack size and its source, internal notes) with a "Priniti details" tab in the product editor; category flags (hide while empty, cover product); Settings > Priniti (enquiry email). |
| `cart.php` | "Pack size" on cart lines and orders for single-size products. |
| `checkout.php` | One "Full name" field (stored as first/last name), Indian mobile and pincode validation, India only, ship to the billing address, no order notes, 10 per line. |
| `forms.php` | Nonce, honeypot, per-IP rate limits, post/redirect/get messages for the storefront forms. |
| `accounts.php` | Sign in with email or mobile; sign up (WooCommerce customer with name and mobile). |
| `contact.php`, `newsletter.php` | Enquiries stored privately and emailed; newsletter subscribers stored, with a hook for an email-marketing integration. |
| `tracking.php` | Order lookup by order ID + billing email or mobile; Packed / Shipped / Delivered order statuses for the journey. |

## Routes

The design's routes are kept. Theme routes and the `/category/` base are registered by the theme itself (rewrite rules flush automatically after a deploy); nothing has to be configured in WordPress for them.

| Route | WordPress |
|---|---|
| `/` | static front page (`front-page.php`) |
| `/shop` | WooCommerce shop page |
| `/category/<slug>` | `product_cat` archive (the theme sets the base to `category`; blog categories move to `/blog-category/`) |
| `/product/<slug>` | single product (product base `product`) |
| `/search?q=` | theme route |
| `/cart`, `/checkout`, `/my-account` | WooCommerce's existing pages, rendered by the theme's templates (their page content is not used, so it does not need to change) |
| `/login`, `/signup`, `/track-order` | theme routes, handled by priniti-core |
| `/about`, `/contact` | theme routes (no WordPress pages needed) |
| `/privacy-policy`, `/terms-and-conditions`, `/shipping-policy`, `/return-refund-policy`, `/cookie-policy` | theme routes (`templates/policy.php`, text in `reference/nextjs/data/legal.ts` → `npm run export:data`); each prints its own canonical link. The former `/terms` and `/return-policy` redirect here (301) |

## Data model (priniti-core)

`plugin/priniti-core/includes/meta-keys.php` lists every field. Product fields mirror `reference/nextjs/types/product.ts`; `_priniti_internal_notes` is stored for the team and never rendered. Pack sizes: one verified size = simple product with `_priniti_pack_size`; two or more = variable product on the global "Pack size" attribute. Category flags (`priniti_hide_when_empty` for Combos, `priniti_origin`, `priniti_cover_product`) mirror `types/category.ts`.

## Local preview and verification

WooCommerce cannot be downloaded in the cloud sandbox, so `tools/dev/local-preview-mu.php` (a must-use plugin, never deployed) feeds the reference catalog (`tools/dev/export-catalog.ts`, optionally with the reference TEST prices) through the theme's own data filters, shims the WooCommerce conditionals and provides an in-memory Store API cart. priniti-core runs for real.

Checked with Playwright against the reference app (`next start`) at 375, 768 and 1280 px: every page and the priced flows (card add-to-cart, quick view, pack size and quantity, cart drawer, cart page, mobile filter sheet) match within a few tenths of a percent of pixels, except intentional copy changes where a feature is now real (e.g. the signup terms links). The WooCommerce checkout and My Account templates can only be verified on a site running WooCommerce (staging).
