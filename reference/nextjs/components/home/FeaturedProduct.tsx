import Link from "next/link";
import { AddToCartButton } from "@/components/commerce/AddToCartButton";
import { PriceDisplay } from "@/components/commerce/PriceDisplay";
import { ProductImage } from "@/components/commerce/ProductImage";
import { RatingStars } from "@/components/commerce/RatingStars";
import { Container } from "@/components/layout/Container";
import { Badge } from "@/components/ui/Badge";
import { ButtonLink } from "@/components/ui/Button";
import { getPurchasableVariant } from "@/lib/product";
import type { Product } from "@/types/product";

export function FeaturedProduct({ product }: { product: Product }) {
  const variant = getPurchasableVariant(product);
  return (
    <section aria-labelledby="featured-heading" className="py-12 lg:py-20">
      <Container>
        <div className="grid overflow-hidden rounded-[2rem] border border-line bg-surface lg:grid-cols-2">
          <div className="relative aspect-[4/3] bg-navy-tint lg:aspect-auto lg:min-h-[30rem]">
            <ProductImage image={product.images[0]} name={product.name} sizes="(min-width:1024px) 45vw, 100vw" />
          </div>
          <div className="flex flex-col justify-center gap-4 p-6 sm:p-10 lg:p-14">
            <div className="flex items-center gap-2">
              <Badge tone="navy">Featured</Badge>
              {product.isMock ? <Badge>Sample</Badge> : null}
            </div>
            <div>
              <Link href={`/category/${product.categorySlug}`} className="text-sm font-medium text-ink-soft hover:text-brand">
                {product.categoryName}
              </Link>
              <h2 id="featured-heading" className="mt-1 font-display text-3xl font-extrabold leading-tight sm:text-4xl">
                {product.name}
              </h2>
            </div>
            {product.reviewCount && product.rating !== undefined ? <RatingStars rating={product.rating} reviewCount={product.reviewCount} size="md" /> : null}
            {variant ? (
              <div>
                <PriceDisplay mrp={variant.mrp ?? variant.price} price={variant.price} size="lg" />
                <p className="mt-1 text-sm text-ink-soft">{variant.label}</p>
              </div>
            ) : null}
            <div className="mt-2 flex flex-col gap-3 sm:flex-row">
              <AddToCartButton product={product} size="lg" fullWidth={false} className="w-full sm:w-auto" />
              <ButtonLink href={`/product/${product.slug}`} variant="secondary" size="lg" className="w-full sm:w-auto">
                View details
              </ButtonLink>
            </div>
          </div>
        </div>
      </Container>
    </section>
  );
}
