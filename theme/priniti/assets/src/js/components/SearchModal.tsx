import { Clock, Search, X } from "lucide-react";
import { useEffect, useMemo, useState } from "react";
import { config, type CategoryLink } from "@theme/config";
import { matchesQuery } from "@theme/lib/search";
import { useRecentSearches } from "@theme/stores/search";
import { useUIStore } from "@theme/stores/ui";
import { Dialog } from "./Dialog";
import { IconButton } from "./IconButton";

/**
 * Port of reference/nextjs/components/layout/SearchModal.tsx.
 * The suggestion index (published WooCommerce products) is fetched once, the first time the dialog opens.
 */
interface SearchIndexItem {
  name: string;
  href: string;
  categoryName: string;
  image: string;
}

let indexPromise: Promise<SearchIndexItem[]> | null = null;
function loadIndex(): Promise<SearchIndexItem[]> {
  if (!config.searchIndexUrl) return Promise.resolve([]);
  indexPromise ??= fetch(config.searchIndexUrl, { credentials: "same-origin" })
    .then((r) => (r.ok ? r.json() : []))
    .catch(() => {
      indexPromise = null;
      return [];
    });
  return indexPromise;
}

const chip = "rounded-full border border-line bg-surface px-3.5 py-2 text-sm font-medium transition-colors hover:border-brand hover:text-brand";

function SearchPanel({ categories }: { categories: CategoryLink[] }) {
  const close = useUIStore((s) => s.closeSearch);
  const recent = useRecentSearches((s) => s.items);
  const addRecent = useRecentSearches((s) => s.add);
  const clearRecent = useRecentSearches((s) => s.clear);
  const [q, setQ] = useState("");
  const [index, setIndex] = useState<SearchIndexItem[]>([]);

  useEffect(() => {
    let alive = true;
    loadIndex().then((items) => alive && setIndex(items));
    return () => {
      alive = false;
    };
  }, []);

  const term = q.trim();
  const productMatches = useMemo(() => (term ? index.filter((i) => matchesQuery(`${i.name} ${i.categoryName}`, term)).slice(0, 6) : []), [index, term]);
  const categoryMatches = useMemo(() => (term ? categories.filter((c) => matchesQuery(c.name, term)).slice(0, 3) : []), [categories, term]);

  const go = (value: string) => {
    const t = value.trim();
    if (!t) return;
    addRecent(t);
    close();
    window.location.assign(`${config.urls.search}?q=${encodeURIComponent(t)}`);
  };

  return (
    <div className="flex max-h-dvh flex-col bg-surface sm:max-h-[80dvh]">
      <form
        role="search"
        onSubmit={(e) => {
          e.preventDefault();
          go(q);
        }}
        className="flex items-center gap-2 border-b border-line px-4 py-3"
      >
        <Search className="size-5 shrink-0 text-ink-soft" aria-hidden />
        <label htmlFor="header-search" className="sr-only">
          Search products
        </label>
        <input
          id="header-search"
          autoFocus
          type="search"
          value={q}
          onChange={(e) => setQ(e.target.value)}
          placeholder="Search snacks or categories"
          autoComplete="off"
          className="h-11 min-w-0 flex-1 bg-transparent text-base outline-none placeholder:text-ink-soft/70"
        />
        <IconButton label="Close search" onClick={close}>
          <X className="size-5" />
        </IconButton>
      </form>

      <div className="flex-1 overflow-y-auto overscroll-contain p-4">
        {term ? (
          <div className="flex flex-col gap-5">
            {categoryMatches.length > 0 ? (
              <div>
                <h2 className="mb-2 text-sm font-semibold text-ink-soft">Categories</h2>
                <ul role="list" className="flex flex-wrap gap-2">
                  {categoryMatches.map((c) => (
                    <li key={c.slug}>
                      <a href={c.href} onClick={close} className={chip}>
                        {c.name}
                      </a>
                    </li>
                  ))}
                </ul>
              </div>
            ) : null}
            {productMatches.length > 0 ? (
              <div>
                <h2 className="mb-2 text-sm font-semibold text-ink-soft">Products</h2>
                <ul role="list" className="flex flex-col">
                  {productMatches.map((p) => (
                    <li key={p.href}>
                      <a
                        href={p.href}
                        onClick={() => {
                          addRecent(term);
                          close();
                        }}
                        className="flex items-center gap-3 rounded-xl p-2 transition-colors hover:bg-brand-tint"
                      >
                        <span className="relative size-12 shrink-0 overflow-hidden rounded-lg border border-line bg-surface">
                          {p.image ? <img src={p.image} alt="" loading="lazy" className="absolute inset-0 size-full object-contain p-0.5" /> : null}
                        </span>
                        <span className="min-w-0">
                          <span className="block truncate text-sm font-semibold">{p.name}</span>
                          <span className="block truncate text-xs text-ink-soft">{p.categoryName}</span>
                        </span>
                      </a>
                    </li>
                  ))}
                </ul>
              </div>
            ) : categoryMatches.length === 0 ? (
              <p className="py-4 text-center text-ink-soft">No suggestions for “{term}”. Press Enter to search anyway.</p>
            ) : null}
            <button type="button" onClick={() => go(q)} className="self-start text-sm font-semibold text-brand underline-offset-4 hover:underline">
              See all results for “{term}”
            </button>
          </div>
        ) : (
          <div className="flex flex-col gap-6">
            {recent.length > 0 ? (
              <div>
                <div className="mb-2 flex items-center justify-between">
                  <h2 className="text-sm font-semibold text-ink-soft">Recent searches</h2>
                  <button type="button" onClick={clearRecent} className="text-xs font-medium text-ink-soft hover:text-brand">
                    Clear
                  </button>
                </div>
                <ul role="list" className="flex flex-wrap gap-2">
                  {recent.map((r) => (
                    <li key={r}>
                      <button type="button" onClick={() => go(r)} className={`${chip} inline-flex items-center gap-1.5`}>
                        <Clock className="size-3.5" aria-hidden />
                        {r}
                      </button>
                    </li>
                  ))}
                </ul>
              </div>
            ) : null}
            <div>
              <h2 className="mb-2 text-sm font-semibold text-ink-soft">Browse categories</h2>
              <ul role="list" className="flex flex-wrap gap-2">
                {categories.map((c) => (
                  <li key={c.slug}>
                    <a href={c.href} onClick={close} className={chip}>
                      {c.name}
                    </a>
                  </li>
                ))}
              </ul>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

export function SearchModal() {
  const open = useUIStore((s) => s.searchOpen);
  const close = useUIStore((s) => s.closeSearch);
  return (
    <Dialog
      open={open}
      onClose={close}
      label="Search"
      className="m-0 h-dvh max-h-none w-full max-w-none overflow-hidden sm:m-auto sm:mt-[8vh] sm:h-auto sm:w-[min(94vw,40rem)] sm:rounded-3xl open:sm:animate-pop"
    >
      <SearchPanel categories={config.categories} />
    </Dialog>
  );
}
