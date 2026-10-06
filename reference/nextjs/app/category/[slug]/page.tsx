import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { CatalogView } from "@/components/catalog/CatalogView";
import { CategoryBanner } from "@/components/catalog/CategoryBanner";
import { Breadcrumbs } from "@/components/layout/Breadcrumbs";
import { Container } from "@/components/layout/Container";
import { ButtonLink } from "@/components/ui/Button";
import { siteConfig } from "@/data/site";
import { getAllCategorySlugs, getCategories, getCategoryBySlug } from "@/lib/api/categories";
import { getCategoryProductCounts } from "@/lib/api/products";
import { parseFilters, type SearchParams } from "@/lib/catalog";

interface Props {
  params: Promise<{ slug: string }>;
  searchParams: Promise<SearchParams>;
}

export async function generateStaticParams() {
  return (await getAllCategorySlugs()).map((slug) => ({ slug }));
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const category = await getCategoryBySlug((await params).slug);
  if (!category) return {};
  const title = category.seo?.title ?? `${category.name} snacks`;
  const description = category.seo?.description ?? category.description ?? `${category.name} from ${siteConfig.name}.`;
  const url = `/category/${category.slug}`;
  return {
    title,
    description,
    alternates: { canonical: url },
    ...(category.published === false ? { robots: { index: false, follow: false } } : {}),
    openGraph: { title, description, url, ...(category.image ? { images: [category.image.src] } : {}) },
  };
}

export default async function CategoryPage({ params, searchParams }: Props) {
  const { slug } = await params;
  const [category, categories, counts, sp] = await Promise.all([
    getCategoryBySlug(slug),
    getCategories(),
    getCategoryProductCounts(),
    searchParams,
  ]);
  if (!category) notFound();

  const filters = parseFilters(sp);
  const published = category.published !== false;
  const index = Math.max(0, categories.findIndex((c) => c.slug === slug));

  return (
    <Container className="py-6 lg:py-10">
      <Breadcrumbs items={[{ label: "Shop", href: "/shop" }, { label: category.name }]} className="mb-4" />
      <CategoryBanner category={category} index={index} productCount={counts[slug] ?? 0} />
      {!published ? (
        <div className="mt-8 flex flex-col items-center gap-4 rounded-card border border-dashed border-line bg-surface px-6 py-14 text-center">
          <p className="font-display text-xl font-semibold">{category.name} are coming soon</p>
          <p className="max-w-md text-ink-soft">
            There are no {category.name.toLowerCase()} to show yet. They will appear here as soon as they are available in the online store.
          </p>
          <ButtonLink href="/shop">Browse all products</ButtonLink>
        </div>
      ) : null}
      {category.subcategories?.length ? (
        <ul role="list" className="mt-6 flex flex-wrap gap-2">
          {category.subcategories.map((s) => (
            <li key={s.slug} className="rounded-full border border-line bg-surface px-4 py-2 text-sm font-medium">
              {s.name}
            </li>
          ))}
        </ul>
      ) : null}
      {published ? (
        <div className="mt-8 lg:mt-10">
          <CatalogView basePath={`/category/${slug}`} filters={filters} categories={categories} lockedCategory={slug} />
        </div>
      ) : null}
    </Container>
  );
}
