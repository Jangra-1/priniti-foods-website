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
    <section aria-labelledby="featured-heading" className="bg-surface py-8 lg:py-10">
      <Container>
        <div className="grid items-center gap-5 overflow-hidden rounded-3xl bg-blush p-4 sm:p-6 md:grid-cols-2 lg:gap-10 lg:p-8">
          <div className="relative aspect-[5/4] overflow-hidden rounded-2xl bg-surface">
            <ProductImage image={product.images[0]} name={product.name} sizes="(min-width:768px) 44vw, 90vw" priority={false} />
          </div>
          <div>
            <div className="flex flex-wrap items-center gap-2">
              <Badge tone="brand">Featured</Badge>
              {product.isMock ? <Badge>Sample</Badge> : null}
            </div>
            <h2 id="featured-heading" className="mt-3 font-display text-2xl font-extrabold sm:text-3xl">
              {product.name}
            </h2>
            <p className="mt-1 text-sm text-ink-soft">{product.categoryName}</p>
            {product.reviewCount && product.rating !== undefined ? <RatingStars rating={product.rating} reviewCount={product.reviewCount} size="md" className="mt-4" /> : null}
            {product.description ? <p className="mt-4 max-w-md text-ink-soft">{product.description}</p> : null}
            {variant ? <PriceDisplay mrp={variant.mrp ?? variant.price} price={variant.price} size="lg" isTest={variant.isTestPrice} className="mt-5" /> : null}
            <div className="mt-5 flex flex-col gap-3 sm:flex-row">
              <AddToCartButton product={product} size="md" fullWidth={false} className="h-12 px-6" />
              <ButtonLink href={`/product/${product.slug}`} variant="secondary" size="md" className="h-12 px-6">
                View details
              </ButtonLink>
            </div>
          </div>
        </div>
      </Container>
    </section>
  );
}
