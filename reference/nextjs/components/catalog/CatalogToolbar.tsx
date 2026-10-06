"use client";

import { Search, SlidersHorizontal } from "lucide-react";
import { useState } from "react";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Drawer } from "@/components/ui/Drawer";
import { clearedFilters, useCatalogParams } from "@/hooks/useCatalogParams";
import { availableSortOptions, countActiveFilters, hasFilterGroups } from "@/lib/catalog";
import type { CatalogCapabilities, CatalogFilters } from "@/types/catalog";
import type { Category } from "@/types/category";
import { FilterFields } from "./FilterFields";

const selectStyles = "h-11 rounded-full border border-line bg-surface px-4 text-sm font-medium";

export function CatalogToolbar({ categories, showCategories, capabilities }: { categories: Category[]; showCategories: boolean; capabilities: CatalogCapabilities }) {
  const { filters, apply } = useCatalogParams();
  const [open, setOpen] = useState(false);
  const [draft, setDraft] = useState<CatalogFilters>(filters);
  const active = countActiveFilters(filters, { ignoreCategories: !showCategories });
  const sortOptions = availableSortOptions(capabilities);
  const groups = hasFilterGroups(capabilities, showCategories);

  const patchDraft = (patch: Partial<CatalogFilters>) => setDraft((d) => ({ ...d, ...patch }));

  return (
    <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
      <form
        role="search"
        className="relative flex-1"
        onSubmit={(e) => {
          e.preventDefault();
          const q = String(new FormData(e.currentTarget).get("q") ?? "").trim();
          apply({ q });
        }}
      >
        <label htmlFor="catalog-search" className="sr-only">
          Search products
        </label>
        <Search className="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-ink-soft" aria-hidden />
        <input
          id="catalog-search"
          key={filters.q}
          name="q"
          type="search"
          defaultValue={filters.q}
          placeholder="Search snacks"
          className="h-11 w-full rounded-full border border-line bg-surface pl-12 pr-4 text-base placeholder:text-ink-soft/70 focus:border-ink"
        />
      </form>

      <div className="flex items-center gap-2">
        <Button
          variant="secondary"
          className="flex-1 sm:flex-none lg:hidden"
          onClick={() => {
            setDraft(filters);
            setOpen(true);
          }}
        >
          <SlidersHorizontal className="size-4" aria-hidden />
          {groups ? "Filters and sort" : "Sort"}
          {active > 0 ? <Badge tone="brand">{active}</Badge> : null}
        </Button>

        <div className="hidden items-center gap-2 sm:flex">
          <label htmlFor="catalog-sort" className="text-sm text-ink-soft">
            Sort by
          </label>
          <select id="catalog-sort" value={filters.sort} onChange={(e) => apply({ sort: e.target.value as CatalogFilters["sort"] })} className={selectStyles}>
            {sortOptions.map((o) => (
              <option key={o.value} value={o.value}>
                {o.label}
              </option>
            ))}
          </select>
        </div>
      </div>

      <Drawer
        open={open}
        onClose={() => setOpen(false)}
        title={groups ? "Filters and sort" : "Sort"}
        side="bottom"
        footer={
          <div className="grid grid-cols-2 gap-2">
            <Button variant="secondary" onClick={() => patchDraft(clearedFilters)}>
              Clear all
            </Button>
            <Button
              onClick={() => {
                apply({ categories: draft.categories, price: draft.price, rating: draft.rating, inStock: draft.inStock, sort: draft.sort });
                setOpen(false);
              }}
            >
              Apply
            </Button>
          </div>
        }
      >
        <div className="flex flex-col gap-6 p-5">
          <fieldset>
            <legend className="mb-2 font-display text-sm font-semibold">Sort by</legend>
            {sortOptions.map((o) => (
              <label key={o.value} className="flex min-h-9 cursor-pointer items-center gap-2.5 text-sm">
                <input type="radio" name="sheet-sort" className="size-4 accent-brand" checked={draft.sort === o.value} onChange={() => patchDraft({ sort: o.value })} />
                {o.label}
              </label>
            ))}
          </fieldset>
          <FilterFields idPrefix="sheet" filters={draft} categories={categories} showCategories={showCategories} capabilities={capabilities} onChange={patchDraft} />
        </div>
      </Drawer>
    </div>
  );
}
