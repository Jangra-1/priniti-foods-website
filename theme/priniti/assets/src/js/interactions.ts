import { config } from "@theme/config";
import { useCartStore } from "@theme/stores/cart";
import { toast } from "@theme/stores/toast";
import { useUIStore } from "@theme/stores/ui";
import { useWishlistStore } from "@theme/stores/wishlist";
import type { QuickViewProduct } from "@theme/types";

/**
 * Behaviour for server-rendered components (the reference's client islands):
 * AddToCartButton, WishlistButton, QuickViewButton, ProductCarousel, FeaturedProducts tabs,
 * ProductGallery, ProductPurchasePanel (pack size, quantity, buy now).
 */

const split = (v: string | undefined) => (v ?? "").split(/\s+/).filter(Boolean);
const swap = (el: Element, on: boolean, onClass?: string, offClass?: string) => {
  el.classList.remove(...split(on ? offClass : onClass));
  el.classList.add(...split(on ? onClass : offClass));
};

interface CartPayload {
  id: number;
  quantity?: number;
  variation?: { attribute: string; value: string }[];
  name?: string;
}

function readPayload(el: HTMLElement, attr: string): CartPayload | null {
  try {
    return JSON.parse(el.getAttribute(attr) ?? "") as CartPayload;
  } catch {
    return null;
  }
}

/** Quantity chosen in the same purchase panel, if any. */
const quantityFor = (el: Element) => Number(el.closest("[data-variant-panel]")?.querySelector("[data-qty-value]")?.textContent ?? 1) || 1;

function flashAdded(btn: HTMLElement) {
  const idle = btn.querySelector<HTMLElement>('[data-when="idle"]');
  const added = btn.querySelector<HTMLElement>('[data-when="added"]');
  if (!idle || !added) return;
  idle.hidden = true;
  added.hidden = false;
  const name = readPayload(btn, "data-priniti-add")?.name ?? "";
  const label = btn.getAttribute("aria-label");
  if (label) btn.setAttribute("aria-label", `${name} added to cart`);
  window.setTimeout(() => {
    idle.hidden = false;
    added.hidden = true;
    if (label) btn.setAttribute("aria-label", label);
  }, 1600);
}

function initCartButtons() {
  document.addEventListener("click", async (e) => {
    const target = e.target as Element | null;

    const add = target?.closest<HTMLButtonElement>("[data-priniti-add]");
    if (add) {
      const payload = readPayload(add, "data-priniti-add");
      if (!payload) return;
      add.disabled = true;
      const ok = await useCartStore.getState().addItem({ ...payload, quantity: quantityFor(add) });
      add.disabled = false;
      if (ok) {
        flashAdded(add);
        if (add.hasAttribute("data-open-cart")) useUIStore.getState().openCart();
      }
      return;
    }

    const buy = target?.closest<HTMLButtonElement>("[data-priniti-buy-now]");
    if (buy) {
      const payload = readPayload(buy, "data-priniti-buy-now");
      if (!payload) return;
      buy.disabled = true;
      const ok = await useCartStore.getState().addItem({ ...payload, quantity: quantityFor(buy), name: undefined });
      buy.disabled = false;
      if (ok) window.location.assign(config.urls.checkout);
    }
  });
}

function initWishlist() {
  const render = () => {
    const ids = useWishlistStore.getState().ids;
    document.querySelectorAll<HTMLButtonElement>("[data-priniti-wishlist]").forEach((btn) => {
      const active = ids.includes(btn.dataset.prinitiWishlist ?? "");
      const name = btn.dataset.name ?? "";
      btn.setAttribute("aria-pressed", String(active));
      btn.setAttribute("aria-label", active ? `Remove ${name} from wishlist` : `Add ${name} to wishlist`);
      btn.querySelector("svg")?.classList.toggle("fill-brand", active);
      btn.querySelector("svg")?.classList.toggle("text-brand", active);
    });
  };
  render();
  useWishlistStore.subscribe(render);
  document.addEventListener("click", (e) => {
    const btn = (e.target as Element | null)?.closest<HTMLButtonElement>("[data-priniti-wishlist]");
    if (!btn) return;
    const id = btn.dataset.prinitiWishlist ?? "";
    const active = useWishlistStore.getState().ids.includes(id);
    useWishlistStore.getState().toggle(id);
    toast({ title: active ? "Removed from wishlist" : "Saved to wishlist", description: btn.dataset.name });
  });
}

function initQuickView() {
  document.addEventListener("click", (e) => {
    const btn = (e.target as Element | null)?.closest<HTMLButtonElement>("[data-priniti-quickview]");
    if (!btn) return;
    try {
      useUIStore.getState().openQuickView(JSON.parse(btn.dataset.prinitiQuickview ?? "") as QuickViewProduct);
    } catch {
      /* malformed payload: ignore */
    }
  });
}

function initCarousels() {
  document.querySelectorAll<HTMLElement>("[data-priniti-carousel]").forEach((root) => {
    const track = root.querySelector<HTMLElement>("[data-carousel-track]");
    const prev = root.querySelector<HTMLButtonElement>("[data-carousel-prev]");
    const next = root.querySelector<HTMLButtonElement>("[data-carousel-next]");
    if (!track) return;
    const update = () => {
      if (prev) prev.disabled = track.scrollLeft <= 4;
      if (next) next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
    };
    update();
    track.addEventListener("scroll", update, { passive: true });
    window.addEventListener("resize", update);
    prev?.addEventListener("click", () => track.scrollBy({ left: -track.clientWidth * 0.85, behavior: "smooth" }));
    next?.addEventListener("click", () => track.scrollBy({ left: track.clientWidth * 0.85, behavior: "smooth" }));
  });
}

function initTabs() {
  document.querySelectorAll<HTMLElement>("[data-priniti-tabs]").forEach((root) => {
    const tabs = root.querySelectorAll<HTMLButtonElement>("[data-tab]");
    tabs.forEach((tab) =>
      tab.addEventListener("click", () => {
        const active = tab.dataset.tab ?? "all";
        tabs.forEach((t) => {
          const on = t === tab;
          t.setAttribute("aria-pressed", String(on));
          swap(t, on, root.dataset.onClass, root.dataset.offClass);
        });
        root.querySelectorAll<HTMLElement>("[data-tab-item]").forEach((item) => {
          item.hidden = active !== "all" && item.dataset.tabItem !== active;
        });
      }),
    );
  });
}

function initGallery() {
  document.querySelectorAll<HTMLElement>("[data-priniti-gallery]").forEach((root) => {
    const main = root.querySelector<HTMLImageElement>("[data-gallery-main] img");
    const thumbs = root.querySelectorAll<HTMLButtonElement>("[data-gallery-thumb]");
    thumbs.forEach((thumb) =>
      thumb.addEventListener("click", () => {
        const img = JSON.parse(thumb.dataset.galleryThumb ?? "{}") as { src: string; srcset?: string; alt: string };
        if (main && img.src) {
          main.src = img.src;
          if (img.srcset) main.srcset = img.srcset;
          else main.removeAttribute("srcset");
          main.alt = img.alt;
        }
        thumbs.forEach((t) => {
          const on = t === thumb;
          t.setAttribute("aria-current", String(on));
          swap(t, on, t.dataset.onClass, t.dataset.offClass);
        });
      }),
    );
  });
}

function initPurchasePanel() {
  document.querySelectorAll<HTMLElement>("[data-priniti-purchase]").forEach((root) => {
    root.querySelectorAll<HTMLInputElement>('input[name="priniti-pack"]').forEach((radio) =>
      radio.addEventListener("change", () => {
        root.querySelectorAll<HTMLElement>("[data-variant-panel]").forEach((p) => (p.hidden = p.dataset.variantPanel !== radio.value));
        root.querySelectorAll<HTMLElement>("[data-variant-only]").forEach((p) => (p.hidden = p.dataset.variantOnly !== radio.value));
      }),
    );
  });

  document.querySelectorAll<HTMLElement>("[data-qty]").forEach((group) => {
    const max = Number(group.dataset.max ?? config.commerce.maxQuantityPerLine);
    const out = group.querySelector<HTMLElement>("[data-qty-value]")!;
    const dec = group.querySelector<HTMLButtonElement>("[data-qty-dec]")!;
    const inc = group.querySelector<HTMLButtonElement>("[data-qty-inc]")!;
    const set = (n: number) => {
      const q = Math.min(Math.max(n, 1), max);
      out.textContent = String(q);
      dec.disabled = q <= 1;
      inc.disabled = q >= max;
    };
    dec.addEventListener("click", () => set(Number(out.textContent) - 1));
    inc.addEventListener("click", () => set(Number(out.textContent) + 1));
    set(1);
  });
}

export function initInteractions() {
  initCartButtons();
  initWishlist();
  initQuickView();
  initCarousels();
  initTabs();
  initGallery();
  initPurchasePanel();
}
