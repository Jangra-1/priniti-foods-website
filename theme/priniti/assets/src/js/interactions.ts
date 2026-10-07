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

/**
 * Product gallery: a scroll-snap track (native swipe on touch), thumbnails, previous / next and dots,
 * all kept in sync with whichever slide is in view.
 */
function initGallery() {
  document.querySelectorAll<HTMLElement>("[data-priniti-gallery]").forEach((root) => {
    const track = root.querySelector<HTMLElement>("[data-gallery-track]");
    if (!track) return;
    const slides = Array.from(track.children) as HTMLElement[];
    const thumbs = root.querySelectorAll<HTMLButtonElement>("[data-gallery-go]");
    const dots = root.querySelectorAll<HTMLElement>("[data-gallery-dot]");
    const prev = root.querySelector<HTMLButtonElement>("[data-gallery-prev]");
    const next = root.querySelector<HTMLButtonElement>("[data-gallery-next]");
    const hint = root.querySelector<HTMLElement>("[data-zoom-hint]");
    let current = 0;

    const go = (i: number) => {
      const target = Math.max(0, Math.min(slides.length - 1, i));
      track.scrollTo({ left: target * track.clientWidth, behavior: "smooth" });
    };
    const mark = (i: number) => {
      current = i;
      thumbs.forEach((t, n) => {
        t.setAttribute("aria-current", String(n === i));
        swap(t, n === i, t.dataset.onClass, t.dataset.offClass);
      });
      dots.forEach((d, n) => {
        d.classList.toggle("w-5", n === i);
        d.classList.toggle("bg-ink", n === i);
        d.classList.toggle("w-1.5", n !== i);
        d.classList.toggle("bg-ink/25", n !== i);
      });
      if (prev) prev.disabled = i === 0;
      if (next) next.disabled = i === slides.length - 1;
      if (hint) hint.hidden = !slides[i]?.hasAttribute("data-zoom");
    };

    let frame = 0;
    track.addEventListener(
      "scroll",
      () => {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(() => {
          const i = Math.round(track.scrollLeft / Math.max(1, track.clientWidth));
          if (i !== current) mark(i);
        });
      },
      { passive: true },
    );
    thumbs.forEach((t) => t.addEventListener("click", () => go(Number(t.dataset.galleryGo))));
    prev?.addEventListener("click", () => go(current - 1));
    next?.addEventListener("click", () => go(current + 1));
    track.addEventListener("keydown", (e) => {
      if (e.key === "ArrowRight") go(current + 1);
      if (e.key === "ArrowLeft") go(current - 1);
    });
    mark(0);
  });
}

/** Hover zoom on the product image (fine pointers only): scale around the pointer position. */
function initZoom() {
  if (!window.matchMedia("(pointer: fine)").matches) return;
  document.querySelectorAll<HTMLElement>("[data-zoom]").forEach((box) => {
    const img = () => box.querySelector<HTMLImageElement>("img");
    box.addEventListener("pointermove", (e) => {
      const el = img();
      if (!el) return;
      const r = box.getBoundingClientRect();
      el.style.transformOrigin = `${((e.clientX - r.left) / r.width) * 100}% ${((e.clientY - r.top) / r.height) * 100}%`;
      el.style.transform = "scale(1.8)";
      box.style.cursor = "zoom-in";
    });
    box.addEventListener("pointerleave", () => {
      const el = img();
      if (el) el.style.transform = "";
    });
  });
}

function initPurchasePanel() {
  document.querySelectorAll<HTMLElement>("[data-priniti-purchase]").forEach((root) => {
    const sticky = document.querySelector<HTMLElement>("[data-sticky-buy]");
    const activePanel = () => root.querySelector<HTMLElement>("[data-variant-panel]:not([hidden])");
    const syncSticky = () => {
      if (!sticky) return;
      const price = activePanel()?.dataset.stickyPrice;
      sticky.querySelectorAll<HTMLButtonElement>("[data-sticky-action]").forEach((b) => (b.disabled = !price));
      const out = sticky.querySelector("[data-sticky-price-out]");
      if (out) out.textContent = price ?? "Price coming soon";
    };
    // 1 / 2 / 3 Packs: the choice is the cart quantity (priniti-core applies the multi-pack price in the cart).
    root.querySelectorAll<HTMLElement>("[data-pack-options]").forEach((group) => {
      const panel = group.closest<HTMLElement>("[data-variant-panel]");
      const qty = group.querySelector<HTMLElement>("[data-qty-value]");
      const total = group.querySelector<HTMLElement>("[data-pack-total-out]");
      group.querySelectorAll<HTMLInputElement>('input[type="radio"]').forEach((radio) =>
        radio.addEventListener("change", () => {
          if (qty) qty.textContent = radio.value;
          if (total) total.textContent = radio.dataset.packTotal ?? "";
          if (panel && radio.dataset.packTotal) panel.dataset.stickyPrice = radio.dataset.packTotal;
          syncSticky();
        }),
      );
    });
    root.querySelectorAll<HTMLInputElement>('input[name="priniti-pack"]').forEach((radio) =>
      radio.addEventListener("change", () => {
        root.querySelectorAll<HTMLElement>("[data-variant-panel]").forEach((p) => (p.hidden = p.dataset.variantPanel !== radio.value));
        root.querySelectorAll<HTMLElement>("[data-variant-only]").forEach((p) => (p.hidden = p.dataset.variantOnly !== radio.value));
        syncSticky();
      }),
    );

    // Sticky mobile buy bar: shown while the panel's own buttons are scrolled out of view; its buttons
    // press the visible panel's buttons, so cart behaviour stays in one place.
    if (sticky) {
      syncSticky();
      sticky.querySelectorAll<HTMLButtonElement>("[data-sticky-action]").forEach((b) =>
        b.addEventListener("click", () => {
          const sel = b.dataset.stickyAction === "buy" ? "[data-priniti-buy-now]" : "[data-priniti-add]";
          activePanel()?.querySelector<HTMLButtonElement>(sel)?.click();
        }),
      );
      const io = new IntersectionObserver(
        ([entry]) => {
          const below = entry.boundingClientRect.top < 0;
          sticky.hidden = entry.isIntersecting || !below;
          document.body.classList.toggle("has-sticky-buy", !sticky.hidden);
        },
        { threshold: 0 },
      );
      io.observe(root);
    }
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

/** Subtle reveal for sections marked data-reveal (CSS in app.css; skipped for reduced motion). */
function initReveal() {
  const items = document.querySelectorAll<HTMLElement>("[data-reveal]");
  if (!items.length) return;
  if (!("IntersectionObserver" in window) || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    items.forEach((el) => el.classList.add("is-visible"));
    return;
  }
  const io = new IntersectionObserver(
    (entries) =>
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add("is-visible");
          io.unobserve(e.target);
        }
      }),
    { rootMargin: "0px 0px -8% 0px" },
  );
  items.forEach((el) => io.observe(el));
}

/** Footer link groups: collapsible on phones, always open from md up (where the summary is not clickable). */
function initFooter() {
  const sections = document.querySelectorAll<HTMLDetailsElement>("[data-footer-section]");
  if (!sections.length) return;
  const mq = window.matchMedia("(min-width: 48rem)");
  const sync = () => sections.forEach((d) => (d.open = mq.matches));
  sync();
  mq.addEventListener("change", sync);
}

/**
 * Homepage hero slider: slides share one grid cell (no layout shift) and cross-fade. Autoplays every data-interval ms,
 * pauses on hover, focus and hidden tabs, never autoplays under reduced motion, and supports dots, prev/next and swipe.
 * Slides 2+ keep their image URLs in data-hero-src until the page has loaded, so slide 1 gets the bandwidth.
 */
function initHeroSlider() {
  document.querySelectorAll<HTMLElement>("[data-hero-slider]").forEach((root) => {
    const slides = [...root.querySelectorAll<HTMLElement>("[data-hero-slide]")];
    const dots = [...root.querySelectorAll<HTMLButtonElement>("[data-hero-dot]")];
    if (slides.length < 2) return;
    const interval = Number(root.dataset.interval) || 3000;
    const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    let current = 0;
    let timer: number | undefined;
    let hovered = false;
    let focused = false;

    const loadImages = () => {
      // <source> first, so the browser picks the right banner size when the <img> gets its src.
      root.querySelectorAll<HTMLSourceElement>("source[data-hero-srcset]").forEach((source) => {
        source.srcset = source.dataset.heroSrcset!;
        source.removeAttribute("data-hero-srcset");
      });
      root.querySelectorAll<HTMLImageElement>("img[data-hero-src]").forEach((img) => {
        if (img.dataset.heroSrcset) img.srcset = img.dataset.heroSrcset;
        img.src = img.dataset.heroSrc!;
        img.removeAttribute("data-hero-src");
      });
    };
    if (document.readyState === "complete") loadImages();
    else window.addEventListener("load", loadImages, { once: true });

    const show = (index: number) => {
      current = (index + slides.length) % slides.length;
      loadImages(); // A visitor may get to slide 2 before the load event.
      slides.forEach((slide, i) => {
        const on = i === current;
        slide.classList.toggle("opacity-0", !on);
        slide.classList.toggle("pointer-events-none", !on);
        slide.classList.toggle("opacity-100", on);
        slide.inert = !on;
        if (on) slide.removeAttribute("aria-hidden");
        else slide.setAttribute("aria-hidden", "true");
        slide.querySelectorAll("a[href]").forEach((a) => (on ? a.removeAttribute("tabindex") : a.setAttribute("tabindex", "-1")));
      });
      dots.forEach((dot, i) => {
        const on = i === current;
        dot.setAttribute("aria-current", String(on));
        dot.classList.toggle("w-6", on);
        dot.classList.toggle("bg-brand", on);
        dot.classList.toggle("w-2", !on);
        dot.classList.toggle("bg-ink/25", !on);
      });
    };
    const stop = () => {
      window.clearInterval(timer);
      timer = undefined;
    };
    const start = () => {
      stop();
      if (!reduced && !hovered && !focused && !document.hidden) timer = window.setInterval(() => show(current + 1), interval);
    };
    const go = (index: number) => {
      show(index);
      start(); // Restart the countdown after a manual change.
    };

    root.querySelector("[data-hero-prev]")?.addEventListener("click", () => go(current - 1));
    root.querySelector("[data-hero-next]")?.addEventListener("click", () => go(current + 1));
    dots.forEach((dot) => dot.addEventListener("click", () => go(Number(dot.dataset.heroDot))));
    root.addEventListener("mouseenter", () => ((hovered = true), stop()));
    root.addEventListener("mouseleave", () => ((hovered = false), start()));
    // Keyboard focus pauses; focus left behind by a mouse or touch tap on the controls does not.
    root.addEventListener("focusin", (e) => {
      focused = (e.target as Element).matches(":focus-visible");
      if (focused) stop();
    });
    root.addEventListener("focusout", (e) => {
      if (!root.contains(e.relatedTarget as Node | null)) (focused = false), start();
    });
    document.addEventListener("visibilitychange", start);

    let x0: number | null = null;
    let y0 = 0;
    root.addEventListener("pointerdown", (e) => {
      if (e.pointerType !== "mouse") (x0 = e.clientX), (y0 = e.clientY);
    }, { passive: true });
    root.addEventListener("pointerup", (e) => {
      if (x0 === null) return;
      const dx = e.clientX - x0;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(e.clientY - y0)) go(current + (dx < 0 ? 1 : -1));
      x0 = null;
    }, { passive: true });
    root.addEventListener("pointercancel", () => (x0 = null));

    start();
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
  initZoom();
  initReveal();
  initFooter();
  initHeroSlider();
}
