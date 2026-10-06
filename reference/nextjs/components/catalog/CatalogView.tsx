import { Suspense, type ReactNode } from "react";
import { ProductGrid } from "@/components/commerce/ProductGrid";
import { ButtonLink } from "@/components/ui/Button";
import { getCatalogCapabilities, getProductList } from "@/lib/api/products";
import { countActiveFilters, hasFilterGroups } from "@/lib/catalog";
import type { CatalogFilters } from "@/types/catalog";
import type { Category } from "@/types/category";
import { CatalogToolbar } from "./CatalogToolbar";
import { FilterSidebar } from "./FilterSidebar";
import { Pagination } from "./Pagination";

interface CatalogViewProps {
  basePath: string;
  filters: CatalogFilters;
  categories: Category[];
  /** Set on category pages: the category is fixed and its filter is hidden. */
  lockedCategory?: string;
  /** Replaces the default empty message (used by search). */
  emptyState?: ReactNode;
}

/** Shared by /shop and /category/[slug]. Filters live in the URL; this renders the result server-side. */
export async function CatalogView({ basePath, filters, categories, lockedCategory, emptyState }: CatalogViewProps) {
  const [result, capabilities] = await Promise.all([getProductList(filters, { lockedCategory }), getCatalogCapabilities()]);
  const showCategories = !lockedCategory;
  const hasSidebar = hasFilterGroups(capabilities, showCategories);
  const isFiltered = filters.q !== "" || countActiveFilters(filters, { ignoreCategories: !showCategories }) > 0;

  return (
    <div className={hasSidebar ? "lg:grid lg:grid-cols-[16rem_minmax(0,1fr)] lg:gap-10" : undefined}>
      <Suspense fallback={null}>
        <FilterSidebar categories={categories} showCategories={showCategories} capabilities={capabilities} />
      </Suspense>

      <div>
        <Suspense fallback={null}>
          <CatalogToolbar categories={categories} showCategories={showCategories} capabilities={capabilities} />
        </Suspense>

        <p aria-live="polite" className="mb-4 text-sm text-ink-soft">
          {result.total} {result.total === 1 ? "product" : "products"}
        </p>

        {result.items.length > 0 ? (
          <ProductGrid products={result.items} className={hasSidebar ? "lg:grid-cols-2 xl:grid-cols-3" : undefined} />
        ) : emptyState ? (
          emptyState
        ) : (
          <div className="rounded-card border border-dashed border-line px-6 py-14 text-center">
            <p className="font-display text-lg font-semibold">No products match</p>
            <p className="mt-1 text-ink-soft">{isFiltered ? "Try removing a filter or searching for something else." : "Products in this category are coming soon."}</p>
            {isFiltered ? (
              <ButtonLink href={basePath} variant="secondary" className="mt-5">
                Clear filters
              </ButtonLink>
            ) : null}
          </div>
        )}

        <Pagination basePath={basePath} filters={filters} page={result.page} pageCount={result.pageCount} />
      </div>
    </div>
  );
}
