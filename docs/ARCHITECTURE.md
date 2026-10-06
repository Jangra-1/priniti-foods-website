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

Status: ✅ ported in phase 1 · ⏳ later phase · ➖ replaced by WooCommerce

| Reference | Theme | Status |
|---|---|---|
| `app/layout.tsx` | `header.php`, `footer.php` | ✅ |
| `layout/Header`, `DesktopNav`, `Logo`, `Container`, `AnnouncementBar` | `template-parts/layout/header.php`, `desktop-nav.php`, `logo.php`, `priniti_container_classes()`, `announcement-bar.php` | ✅ |
| `layout/MobileNavigation`, `SearchModal` | islands `MobileNavigation.tsx`, `SearchModal.tsx` | ✅ |
| `layout/Footer` | `template-parts/layout/footer.php` | ✅ |
| `layout/PageHeader`, `Breadcrumbs`, `ui/Eyebrow` | `template-parts/layout/page-header.php`, `breadcrumbs.php`, `template-parts/ui/eyebrow.php` | ✅ |
| `commerce/CartDrawer`, `CartItem`, `QuantitySelector`, `PriceDisplay`, `ProductImage` | islands (Store API) | ✅ |
| `ui/Toaster` | island | ✅ |
| `app/not-found.tsx` | `404.php` | ✅ |
| `layout/TestPriceBanner`, `commerce/TestPriceBadge`, `data/test-prices.ts` | not ported: WooCommerce prices are real; test prices live on staging only | ➖ |
| `sections/*` (homepage) | `front-page.php` + `template-parts/sections/*` | ⏳ homepage phase |
| `catalog/*`, `app/shop`, `app/category/[slug]`, `app/search` | WooCommerce archive templates + URL filters | ⏳ catalog phase |
| `commerce/ProductCard`, `ProductGrid`, `ProductCarousel`, `QuickView*`, `WishlistButton` | PHP card + islands | ⏳ catalog phase |
| `app/product/[slug]`, `ProductGallery`, `ProductPurchasePanel`, `PackSizeSelector`, `ProductDetails` | `single-product` templates + islands | ⏳ product phase |
| `commerce/CartView`, `OrderSummary`, `app/cart` | cart template override | ⏳ cart phase |
| `checkout/*`, `lib/order.ts`, `types/order.ts` | WooCommerce classic checkout, template overrides | ➖/⏳ checkout phase |
| `account/*`, `track/*` | my-account templates, login/signup pages, order tracking | ⏳ accounts phase |
| `about/*`, `contact/*`, `legal/*`, `data/company.ts`, `data/legal.ts` | page templates (+ contact handler in priniti-core) | ⏳ content phase |
| `components/home/*` | not ported: unused duplicates of `components/sections/*` in the reference | ➖ |

## Routes

The design's routes are kept. Mapping (set in WooCommerce/WordPress settings during the configuration phase, after approval):

| Route | WordPress |
|---|---|
| `/` | static front page (`front-page.php`) |
| `/shop` | WooCommerce shop page |
| `/category/<slug>` | `product_cat` archive (category base `category`; the blog category base moves to avoid a clash) |
| `/product/<slug>` | single product (product base `product`) |
| `/search?q=` | theme route (catalog phase) |
| `/cart`, `/checkout` | WooCommerce pages (classic shortcodes, for template overrides) |
| `/login`, `/signup`, `/track-order` | pages using WooCommerce account and order-tracking handlers |
| `/about`, `/contact`, `/privacy-policy`, `/terms`, `/shipping-policy`, `/return-policy` | pages with dedicated templates |

## Data model (priniti-core)

`plugin/priniti-core/includes/meta-keys.php` lists every field. Product fields mirror `reference/nextjs/types/product.ts`; `_priniti_internal_notes` is stored for the team and never rendered. Pack sizes: one verified size = simple product with `_priniti_pack_size`; two or more = variable product on the global "Pack size" attribute. Category flags (`priniti_hide_when_empty` for Combos, `priniti_origin`, `priniti_cover_product`) mirror `types/category.ts`.

## Local preview

WooCommerce cannot be downloaded in the cloud sandbox, so `tools/dev/local-preview-mu.php` (a must-use plugin, never deployed) fakes the `product_cat` taxonomy and an in-memory Store API cart. Visual parity is checked with Playwright screenshots of the reference (`next start`) and the theme at 375, 768 and 1280 px.
