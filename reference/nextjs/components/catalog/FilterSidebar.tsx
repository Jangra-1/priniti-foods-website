"use client";

import { clearedFilters, useCatalogParams } from "@/hooks/useCatalogParams";
import { countActiveFilters, hasFilterGroups } from "@/lib/catalog";
import type { CatalogCapabilities } from "@/types/catalog";
import type { Category } from "@/types/category";
import { FilterFields } from "./FilterFields";

export function FilterSidebar({ categories, showCategories, capabilities }: { categories: Category[]; showCategories: boolean; capabilities: CatalogCapabilities }) {
  const { filters, apply } = useCatalogParams();
  const active = countActiveFilters(filters, { ignoreCategories: !showCategories });
  if (!hasFilterGroups(capabilities, showCategories)) return null;

  return (
    <aside aria-label="Filters" className="hidden lg:block">
      <div className="sticky top-28 rounded-card border border-line bg-surface p-5">
        <div className="mb-5 flex items-center justify-between">
          <h2 className="font-display text-lg font-semibold">Filters</h2>
          {active > 0 ? (
            <button type="button" onClick={() => apply(clearedFilters)} className="text-sm font-medium text-brand underline-offset-4 hover:underline">
              Clear all
            </button>
          ) : null}
        </div>
        <FilterFields idPrefix="side" filters={filters} categories={categories} showCategories={showCategories} capabilities={capabilities} onChange={apply} />
      </div>
    </aside>
  );
}
