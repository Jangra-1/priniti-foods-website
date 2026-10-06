import type { Metadata } from "next";
import Link from "next/link";
import { CatalogView } from "@/components/catalog/CatalogView";
import { Breadcrumbs } from "@/components/layout/Breadcrumbs";
import { Container } from "@/components/layout/Container";
import { ButtonLink } from "@/components/ui/Button";
import { getCategories } from "@/lib/api/categories";
import { parseFilters, type SearchParams } from "@/lib/catalog";
import { matchesQuery } from "@/lib/search";

export const metadata: Metadata = {
  title: "Search",
  description: "Search Priniti Foods snacks by product name or category.",
  robots: { index: false, follow: true }, // search result pages should never be indexed
};

const chip = "rounded-full border border-line bg-surface px-4 py-2 text-sm font-medium transition-colors hover:border-brand hover:text-brand";

export default async function SearchPage({ searchParams }: { searchParams: Promise<SearchParams> }) {
  const filters = parseFilters(await searchParams);
  const categories = await getCategories();
  const q = filters.q;
  const matchingCategories = q ? categories.filter((c) => matchesQuery(c.name, q)) : [];

  return (
    <Container className="py-6 lg:py-10">
      <Breadcrumbs items={[{ label: "Search" }]} />
      <h1 className="mb-6 mt-4 font-display text-3xl font-extrabold sm:text-4xl">{q ? `Results for “${q}”` : "Search"}</h1>

      {!q ? (
        <div className="rounded-card border border-line bg-surface p-6 sm:p-8">
          <form action="/search" role="search" className="flex flex-col gap-3 sm:flex-row">
            <label htmlFor="search-page-input" className="sr-only">
              Search products
            </label>
            <input
              id="search-page-input"
              name="q"
              type="search"
              placeholder="Search by product or category"
              className="h-12 flex-1 rounded-full border border-line bg-surface px-5 text-base placeholder:text-ink-soft/70 focus:border-ink"
            />
            <button type="submit" className="h-12 rounded-full bg-brand px-7 text-sm font-semibold text-white hover:bg-brand-dark">
              Search
            </button>
          </form>
          <h2 className="mb-3 mt-8 font-display text-lg font-semibold">Browse by category</h2>
          <ul role="list" className="flex flex-wrap gap-2">
            {categories.map((c) => (
              <li key={c.slug}>
                <Link href={`/category/${c.slug}`} className={chip}>
                  {c.name}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      ) : (
        <>
          {matchingCategories.length > 0 ? (
            <div className="mb-6">
              <h2 className="mb-2 text-sm font-semibold text-ink-soft">Matching categories</h2>
              <ul role="list" className="flex flex-wrap gap-2">
                {matchingCategories.map((c) => (
                  <li key={c.slug}>
                    <Link href={`/category/${c.slug}`} className={chip}>
                      {c.name}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ) : null}
          <CatalogView
            basePath="/search"
            filters={filters}
            categories={categories}
            emptyState={
              <div className="rounded-card border border-dashed border-line px-6 py-14 text-center">
                <p className="font-display text-lg font-semibold">No products found for “{q}”</p>
                <ul role="list" className="mx-auto mt-3 max-w-sm space-y-1 text-ink-soft">
                  <li>Check the spelling or try a shorter word.</li>
                  <li>Search by product name (for example “jeera”) or by category.</li>
                </ul>
                <h2 className="mb-3 mt-6 text-sm font-semibold text-ink-soft">Browse a category</h2>
                <ul role="list" className="flex flex-wrap justify-center gap-2">
                  {categories.map((c) => (
                    <li key={c.slug}>
                      <Link href={`/category/${c.slug}`} className={chip}>
                        {c.name}
                      </Link>
                    </li>
                  ))}
                </ul>
                <ButtonLink href="/shop" className="mt-6">
                  Browse all products
                </ButtonLink>
              </div>
            }
          />
        </>
      )}
    </Container>
  );
}
