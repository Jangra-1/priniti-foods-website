import type { Metadata } from "next";
import { ProductCard } from "@/components/commerce/ProductCard";
import { ProductCarousel } from "@/components/commerce/ProductCarousel";
import { ProductGrid } from "@/components/commerce/ProductGrid";
import { Container } from "@/components/layout/Container";
import { JsonLd } from "@/components/layout/JsonLd";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { BrandStory } from "@/components/sections/BrandStory";
import { CategorySection } from "@/components/sections/CategorySection";
import { FeaturedProduct } from "@/components/sections/FeaturedProduct";
import { FeaturedProducts } from "@/components/sections/FeaturedProducts";
import { Hero } from "@/components/sections/Hero";
import { NewsletterSignup } from "@/components/sections/NewsletterSignup";
import { PromoBanner } from "@/components/sections/PromoBanner";
import { PromoTiles } from "@/components/sections/PromoTiles";
import { ReviewsSection } from "@/components/sections/ReviewsSection";
import { SocialGrid } from "@/components/sections/SocialGrid";
import { StatsBand } from "@/components/sections/StatsBand";
import { WhyPriniti } from "@/components/sections/WhyPriniti";
import { homeContent } from "@/data/home";
import { merchandising } from "@/data/merchandising";
import { siteConfig } from "@/data/site";
import { getCategories } from "@/lib/api/categories";
import { getCategoryProductCounts, getProducts, getProductsBySlugs } from "@/lib/api/products";
import { getFeaturedReviews } from "@/lib/api/reviews";
import { organizationJsonLd, websiteJsonLd } from "@/lib/seo";

export const metadata: Metadata = {
  title: { absolute: `${siteConfig.name} | Snacks for every moment` },
  description: siteConfig.description,
  alternates: { canonical: "/" },
  openGraph: { title: `${siteConfig.name} | Snacks for every moment`, description: siteConfig.description, url: "/" },
};

/**
 * Homepage, in the section order of the approved design.
 * Sections that would normally depend on sales / popularity / launch data use neutral, hand-curated labels
 * unless that data really exists. Every product shown is a real, published catalog product.
 */
export default async function HomePage() {
  const [categories, counts, realBestSellers, realNew, heroPacks, storyPacks, picks, range, sweetsBakery, featured, combos, reviews] = await Promise.all([
    getCategories(),
    getCategoryProductCounts(),
    getProducts({ collection: "best-sellers", limit: 8 }),
    getProducts({ collection: "new-arrivals", limit: 4 }),
    getProductsBySlugs([...merchandising.heroSlugs]),
    getProductsBySlugs([...merchandising.storySlugs]),
    getProductsBySlugs([...merchandising.curatedPicks.slugs]),
    getProductsBySlugs([...merchandising.explore.slugs]),
    getProductsBySlugs([...merchandising.sweetsBakery.slugs]),
    getProductsBySlugs([merchandising.featured.slug]),
    getProducts({ category: "combos", limit: 3 }),
    getFeaturedReviews(),
  ]);

  const { promo, newsletter } = homeContent;
  const carousel = realBestSellers.length
    ? { eyebrow: "Top picks", title: "Best Sellers", description: "The snacks customers reach for most.", items: realBestSellers }
    : { eyebrow: "From every category", title: merchandising.explore.title, description: merchandising.explore.description, items: range };

  const tile = (slug: string, eyebrow: string, title: string, cta: string, tone: "navy" | "leaf") => ({
    eyebrow,
    title,
    cta,
    tone,
    href: `/category/${slug}`,
    products: sweetsBakery.filter((p) => p.categorySlug === slug),
  });

  return (
    <>
      <JsonLd data={organizationJsonLd()} />
      <JsonLd data={websiteJsonLd()} />

      <Hero products={heroPacks} categories={categories} />
      <CategorySection categories={categories} counts={counts} />

      {/* "Featured Products": hand-picked (manual selection), with category tabs over the existing ProductCard */}
      <FeaturedProducts
        eyebrow="Handpicked for you"
        title="Featured Products"
        href="/shop"
        items={picks.map((p) => ({ key: p.id, category: { slug: p.categorySlug, name: p.categoryName }, node: <ProductCard product={p} /> }))}
      />

      <WhyPriniti />

      {carousel.items.length > 0 ? (
        <section aria-labelledby="carousel-heading" className="bg-canvas py-8 lg:py-10">
          <Container>
            <ProductCarousel
              label={carousel.title}
              header={
                <>
                  <Eyebrow className="mb-2">{carousel.eyebrow}</Eyebrow>
                  <h2 id="carousel-heading" className="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
                    {carousel.title}
                  </h2>
                </>
              }
            >
              {carousel.items.map((p) => (
                <ProductCard key={p.id} product={p} />
              ))}
            </ProductCarousel>
          </Container>
        </section>
      ) : null}

      {/* Combos is ecommerce-only and unpublished: "coming soon" until real combo products exist */}
      <PromoBanner
        headline={promo.headline}
        description={promo.description}
        cta={promo.cta}
        badge={combos.length === 0 ? promo.badge : undefined}
        products={combos}
        visualProducts={heroPacks.slice(0, 3)}
      />

      <PromoTiles
        tiles={[
          tile("sweets", "Mithai", "Sweets", "Shop sweets", "navy"),
          tile("cookies", "Bakery", "Cookies", "Shop cookies", "leaf"),
        ]}
      />

      {realNew.length > 0 ? (
        <section aria-labelledby="new-heading" className="bg-canvas py-8 lg:py-10">
          <Container>
            <SectionHeading id="new-heading" eyebrow="Just in" title="New Arrivals" href="/shop?collection=new-arrivals" className="mb-5" />
            <ProductGrid products={realNew} className="md:grid-cols-4" />
          </Container>
        </section>
      ) : null}

      {featured[0] ? <FeaturedProduct product={featured[0]} /> : null}
      <ReviewsSection reviews={reviews} />

      <StatsBand
        title="The Priniti range"
        description="Snacks, sweets and bakery from one brand."
        stats={[
          { value: String(Object.values(counts).reduce((a, b) => a + b, 0)), label: "Products online" },
          { value: String(categories.length), label: "Categories" },
          { value: "No.1", label: "Swad Mein" },
        ]}
      />

      <BrandStory products={storyPacks} />
      <NewsletterSignup headline={newsletter.headline} copy={newsletter.copy} />
      <SocialGrid products={range} />
    </>
  );
}
