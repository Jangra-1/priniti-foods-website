/**
 * Shop / category / search filters (CatalogToolbar, FilterSidebar, FilterFields, useCatalogParams).
 * The URL is the single source of truth: every change navigates to the same query string the reference builds
 * (q, collection, category, price, rating, stock, sort, page). Any change resets to page 1.
 */

interface State {
  q: string;
  collection: string | null;
  category: string[];
  price: string | null;
  rating: number | null;
  stock: boolean;
  sort: string;
}

function toQuery(s: State): string {
  const p = new URLSearchParams();
  if (s.q) p.set("q", s.q);
  if (s.collection) p.set("collection", s.collection);
  if (s.category.length) p.set("category", s.category.join(","));
  if (s.price) p.set("price", s.price);
  if (s.rating) p.set("rating", String(s.rating));
  if (s.stock) p.set("stock", "1");
  if (s.sort && s.sort !== "featured") p.set("sort", s.sort);
  const qs = p.toString().replace(/%2C/g, ",");
  return qs ? `?${qs}` : "";
}

/** Reads the filter controls inside one scope (sidebar or mobile sheet). */
function readScope(scope: HTMLElement, base: State): State {
  const next: State = { ...base };
  const cats = scope.querySelectorAll<HTMLInputElement>('input[name="category"]');
  if (cats.length) next.category = Array.from(cats).filter((c) => c.checked).map((c) => c.value);
  const pick = (name: string) => scope.querySelector<HTMLInputElement>(`input[data-name="${name}"]:checked`)?.value;
  if (scope.querySelector('input[data-name="price"]')) next.price = pick("price") || null;
  if (scope.querySelector('input[data-name="rating"]')) next.rating = Number(pick("rating")) || null;
  if (scope.querySelector('input[data-name="sort"]')) next.sort = pick("sort") || "featured";
  const stock = scope.querySelector<HTMLInputElement>('input[name="stock"]');
  if (stock) next.stock = stock.checked;
  return next;
}

const cleared = (s: State): State => ({ ...s, category: [], price: null, rating: null, stock: false });

export function initCatalog() {
  document.querySelectorAll<HTMLElement>("[data-priniti-catalog]").forEach((root) => {
    const base = root.dataset.base ?? window.location.pathname;
    const state = JSON.parse(root.dataset.state ?? "{}") as State;
    const go = (s: State) => window.location.assign(`${base}${toQuery(s)}`);

    // Desktop sidebar: applies instantly.
    const side = root.querySelector<HTMLElement>('[data-filter-scope="instant"]');
    side?.addEventListener("change", () => go(readScope(side, state)));
    side?.querySelector("[data-filter-clear]")?.addEventListener("click", () => go(cleared(state)));

    // Toolbar search keeps the other filters (apply({ q })).
    root.querySelector<HTMLFormElement>("[data-catalog-search]")?.addEventListener("submit", (e) => {
      e.preventDefault();
      const q = String(new FormData(e.currentTarget as HTMLFormElement).get("q") ?? "").trim();
      go({ ...state, q });
    });

    root.querySelector<HTMLSelectElement>("[data-catalog-sort]")?.addEventListener("change", (e) => go({ ...state, sort: (e.target as HTMLSelectElement).value }));

    // Mobile sheet: edits a draft, applies on "Apply".
    const sheet = root.querySelector<HTMLDialogElement>("[data-filter-sheet]");
    const sheetScope = sheet?.querySelector<HTMLElement>('[data-filter-scope="sheet"]');
    root.querySelector("[data-filter-open]")?.addEventListener("click", () => sheet?.showModal());
    sheet?.querySelector("[data-filter-close]")?.addEventListener("click", () => sheet.close());
    sheet?.addEventListener("click", (e) => {
      if (e.target === sheet) sheet.close();
    });
    sheet?.querySelector("[data-filter-apply]")?.addEventListener("click", () => sheetScope && go(readScope(sheetScope, state)));
    sheet?.querySelector("[data-filter-reset]")?.addEventListener("click", () => {
      sheetScope?.querySelectorAll<HTMLInputElement>('input[name="category"], input[name="stock"]').forEach((i) => (i.checked = false));
      sheetScope?.querySelectorAll<HTMLInputElement>('input[data-name="price"][value=""], input[data-name="rating"][value=""]').forEach((i) => (i.checked = true));
    });
  });
}
