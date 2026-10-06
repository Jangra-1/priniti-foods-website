/** Server-provided configuration (window.PRINITI, printed by inc/assets.php). Public values only. */
export interface NavLink {
  label: string;
  href: string;
}

export interface CategoryLink {
  slug: string;
  name: string;
  href: string;
}

export interface PrinitiConfig {
  siteName: string;
  urls: Record<"home" | "shop" | "cart" | "checkout" | "login" | "signup" | "search" | "account", string>;
  nav: { mobileShop: NavLink[]; mobileInfo: NavLink[] };
  categories: CategoryLink[];
  searchIndexUrl: string;
  storeApi: { enabled: boolean; root: string; nonce: string };
  commerce: {
    currency: string;
    freeShippingThreshold: number | null;
    maxQuantityPerLine: number;
    pricesIncludeTax: boolean;
    couponsEnabled: boolean;
    /** Checkout readiness from the live WooCommerce settings (inc/woocommerce.php priniti_checkout_status()). */
    status: { shipping: boolean; tax: boolean; payment: boolean };
  };
}

declare global {
  interface Window {
    PRINITI?: PrinitiConfig;
  }
}

const fallback: PrinitiConfig = {
  siteName: "Priniti Foods",
  urls: { home: "/", shop: "/shop/", cart: "/cart/", checkout: "/checkout/", login: "/login/", signup: "/signup/", search: "/search/", account: "/login/" },
  nav: { mobileShop: [], mobileInfo: [] },
  categories: [],
  searchIndexUrl: "",
  storeApi: { enabled: false, root: "", nonce: "" },
  commerce: { currency: "INR", freeShippingThreshold: null, maxQuantityPerLine: 10, pricesIncludeTax: false, couponsEnabled: false, status: { shipping: false, tax: false, payment: false } },
};

export const config: PrinitiConfig = window.PRINITI ?? fallback;
