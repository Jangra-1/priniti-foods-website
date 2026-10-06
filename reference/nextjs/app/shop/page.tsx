import type { Metadata } from "next";
import { CatalogView } from "@/components/catalog/CatalogView";
import { Breadcrumbs } from "@/components/layout/Breadcrumbs";
import { Container } from "@/components/layout/Container";
import { getCategories } from "@/lib/api/categories";
import { getShopHeading, parseFilters, type SearchParams } from "@/lib/catalog";

export const metadata: Metadata = {
  title: "Shop all snacks",
  description: "Browse namkeen, chips, puffs, popcorn, sweets, cookies, rusk and combo packs from Priniti Foods.",
  alternates: { canonical: "/shop" }, // filters and sort are not part of the canonical URL
  openGraph: { title: "Shop all snacks", url: "/shop" },
};

export default async function ShopPage({ searchParams }: { searchParams: Promise<SearchParams> }) {
  const filters = parseFilters(await searchParams);
  const categories = await getCategories();

  return (
    <Container className="py-6 lg:py-10">
      <Breadcrumbs items={[{ label: "Shop" }]} />
      <h1 className="mb-6 mt-4 font-display text-3xl font-extrabold sm:text-4xl">{getShopHeading(filters)}</h1>
      <CatalogView basePath="/shop" filters={filters} categories={categories} />
    </Container>
  );
}
