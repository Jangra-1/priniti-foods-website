import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ProductCard } from "@/components/commerce/ProductCard";
import { ProductDetails } from "@/components/commerce/ProductDetails";
import { ProductGallery } from "@/components/commerce/ProductGallery";
import { ProductPurchasePanel } from "@/components/commerce/ProductPurchasePanel";
import { RatingStars } from "@/components/commerce/RatingStars";
import { Breadcrumbs } from "@/components/layout/Breadcrumbs";
import { Container } from "@/components/layout/Container";
import { JsonLd } from "@/components/layout/JsonLd";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { siteConfig } from "@/data/site";
import { getProductBySlug, getPublishedSlugs, getRelatedProducts } from "@/lib/api/products";
import { productJsonLd } from "@/lib/seo";

interface Props {
  params: Promise<{ slug: string }>;
}

export async function generateStaticParams() {
  return (await getPublishedSlugs()).map((slug) => ({ slug }));
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const product = await getProductBySlug((await params).slug);
  if (!product) return {};
  const title = product.seo?.title ?? `${product.name} | ${product.categoryName}`;
  const description = product.seo?.description ?? product.description ?? `${product.name} by ${siteConfig.name}.`;
  const url = `/product/${product.slug}`;
  return {
    title,
    description,
    alternates: { canonical: url },
    openGraph: { title, description, url, ...(product.images[0] ? { images: [product.images[0].src] } : {}) },
  };
}

export default async function ProductPage({ params }: Props) {
  const { slug } = await params;
  const product = await getProductBySlug(slug);
  if (!product) notFound();
  const related = await getRelatedProducts(slug, 4);

  return (
    <Container className="py-6 lg:py-10">
      <JsonLd data={productJsonLd(product)} />
      <Breadcrumbs
        items={[
          { label: "Shop", href: "/shop" },
          { label: product.categoryName, href: `/category/${product.categorySlug}` },
          { label: product.name },
        ]}
      />

      <div className="mt-6 grid gap-8 lg:grid-cols-[1.05fr_1fr] lg:gap-14">
        <ProductGallery images={product.images} name={product.name} />
        <div className="flex flex-col gap-5">
          <div>
            <Link href={`/category/${product.categorySlug}`} className="text-sm font-medium text-ink-soft hover:text-brand">
              {product.categoryName}
            </Link>
            <h1 className="mt-1 font-display text-3xl font-extrabold leading-tight sm:text-4xl">{product.name}</h1>
            {product.reviewCount && product.rating !== undefined ? (
              <RatingStars rating={product.rating} reviewCount={product.reviewCount} size="md" className="mt-3" />
            ) : null}
          </div>
          <ProductPurchasePanel product={product} />
        </div>
      </div>

      <div className="mt-12 lg:mt-16">
        <ProductDetails product={product} />
      </div>

      {related.length > 0 ? (
        <section aria-labelledby="related-heading" className="mt-14 lg:mt-20">
          <SectionHeading id="related-heading" title="You may also like" href={`/category/${product.categorySlug}`} linkLabel={`More ${product.categoryName}`} className="mb-6" />
          <ul role="list" className="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-4">
            {related.map((p) => (
              <li key={p.id}>
                <ProductCard product={p} />
              </li>
            ))}
          </ul>
        </section>
      ) : null}
    </Container>
  );
}
