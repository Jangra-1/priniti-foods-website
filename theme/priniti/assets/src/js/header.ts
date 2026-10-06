import { useCartStore } from "@theme/stores/cart";
import { useUIStore } from "@theme/stores/ui";

/**
 * Behaviour for the server-rendered header (template-parts/layout/header.php):
 * scrolled styling (useScrolled), the categories menu (DesktopNav CategoriesMenu), the buttons that open
 * overlays, and the cart badge (Header.tsx `badge={count}`).
 */

const split = (value: string | undefined) => (value ?? "").split(/\s+/).filter(Boolean);

function initScrolled(header: HTMLElement, threshold = 8) {
  const top = split(header.dataset.topClass);
  const scrolled = split(header.dataset.scrolledClass);
  let state: boolean | null = null;
  const update = () => {
    const next = window.scrollY > threshold;
    if (next === state) return;
    state = next;
    header.classList.remove(...(next ? top : scrolled));
    header.classList.add(...(next ? scrolled : top));
  };
  update();
  window.addEventListener("scroll", update, { passive: true });
}

function initCategoriesMenu(root: HTMLElement) {
  const toggle = root.querySelector<HTMLButtonElement>("[data-priniti-menu-toggle]");
  const panel = root.querySelector<HTMLElement>("[data-priniti-menu-panel]");
  const chevron = root.querySelector<HTMLElement>("[data-open-class]");
  if (!toggle || !panel) return;

  const set = (open: boolean) => {
    panel.hidden = !open;
    toggle.setAttribute("aria-expanded", String(open));
    if (chevron) chevron.classList.toggle(chevron.dataset.openClass ?? "", open);
  };

  root.addEventListener("mouseenter", () => set(true));
  root.addEventListener("mouseleave", () => set(false));
  toggle.addEventListener("click", () => set(panel.hidden));
  root.addEventListener("focusout", (e) => {
    if (!root.contains(e.relatedTarget as Node | null)) set(false);
  });
  root.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      set(false);
      toggle.focus();
    }
  });
  panel.querySelectorAll("a").forEach((a) => a.addEventListener("click", () => set(false)));
}

function initOpenButtons() {
  const ui = useUIStore.getState();
  const actions: Record<string, () => void> = {
    cart: ui.openCart,
    search: ui.openSearch,
    "mobile-nav": ui.openMobileNav,
  };
  document.addEventListener("click", (e) => {
    const trigger = (e.target as Element | null)?.closest<HTMLElement>("[data-priniti-open]");
    const action = trigger && actions[trigger.dataset.prinitiOpen ?? ""];
    if (action) {
      e.preventDefault();
      action();
    }
  });
}

function initCartBadge() {
  const badges = document.querySelectorAll<HTMLElement>("[data-priniti-cart-count]");
  const buttons = document.querySelectorAll<HTMLElement>("[data-priniti-cart-button]");
  const render = (count: number) => {
    badges.forEach((b) => {
      b.hidden = !count;
      b.textContent = count > 99 ? "99+" : String(count);
    });
    buttons.forEach((b) => b.setAttribute("aria-label", count ? `Cart, ${count} ${count === 1 ? "item" : "items"}` : "Cart"));
  };
  render(useCartStore.getState().itemCount);
  useCartStore.subscribe((s, prev) => {
    if (s.itemCount !== prev.itemCount) render(s.itemCount);
  });
}

export function initHeader() {
  const header = document.querySelector<HTMLElement>("[data-priniti-header]");
  if (header) initScrolled(header);
  document.querySelectorAll<HTMLElement>("[data-priniti-menu]").forEach(initCategoriesMenu);
  initOpenButtons();
  initCartBadge();
}
