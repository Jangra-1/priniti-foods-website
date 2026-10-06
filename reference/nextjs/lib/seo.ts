import { siteConfig } from "@/data/site";
import type { Product } from "@/types/product";

export const absoluteUrl = (path = "/") => new URL(path, siteConfig.url).toString();

export function breadcrumbJsonLd(items: { label: string; href?: string }[]) {
  return {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    itemListElement: items.map((item, i) => ({
      "@type": "ListItem",
      position: i + 1,
      name: item.label,
      ...(item.href ? { item: absoluteUrl(item.href) } : {}),
    })),
  };
}

export function organizationJsonLd() {
  return {
    "@context": "https://schema.org",
    "@type": "Organization",
    name: siteConfig.name,
    legalName: siteConfig.legalName,
    url: siteConfig.url,
    ...(siteConfig.logo ? { logo: absoluteUrl(siteConfig.logo.src) } : {}),
  };
}

export function websiteJsonLd() {
  return {
    "@context": "https://schema.org",
    "@type": "WebSite",
    name: siteConfig.name,
    url: siteConfig.url,
    potentialAction: {
      "@type": "SearchAction",
      target: `${absoluteUrl("/shop")}?q={search_term_string}`,
      "query-input": "required name=search_term_string",
    },
  };
}

export function itemListJsonLd(items: { name: string; href: string }[]) {
  return {
    "@context": "https://schema.org",
    "@type": "ItemList",
    itemListElement: items.map((item, i) => ({
      "@type": "ListItem",
      position: i + 1,
      name: item.name,
      url: absoluteUrl(item.href),
    })),
  };
}

export function productJsonLd(product: Product) {
  // TEST prices are development-only and must never reach structured data.
  const priced = product.variants.filter((v) => v.price !== undefined && !v.isTestPrice);
  return {
    "@context": "https://schema.org",
    "@type": "Product",
    name: product.name,
    brand: { "@type": "Brand", name: siteConfig.name },
    category: product.categoryName,
    url: absoluteUrl(`/product/${product.slug}`),
    ...(product.images.length ? { image: product.images.map((i) => absoluteUrl(i.src)) } : {}),
    ...(product.description ? { description: product.description } : {}),
    // Offers are only emitted for variants with a real price. None exist yet, so none are claimed.
    ...(priced.length
      ? {
          offers: priced.map((v) => ({
            "@type": "Offer",
            priceCurrency: siteConfig.commerce.currency,
            price: v.price,
            ...(v.sku ? { sku: v.sku } : {}),
            url: absoluteUrl(`/product/${product.slug}`),
          })),
        }
      : {}),
  };
}
