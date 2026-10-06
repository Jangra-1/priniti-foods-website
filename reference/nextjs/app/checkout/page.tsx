import type { Metadata } from "next";
import { CheckoutView } from "@/components/checkout/CheckoutView";
import { Breadcrumbs } from "@/components/layout/Breadcrumbs";
import { Container } from "@/components/layout/Container";
import { getPriceIndex } from "@/lib/api/products";

export const metadata: Metadata = {
  title: "Checkout (development)",
  robots: { index: false, follow: false },
};

export default async function CheckoutPage() {
  const priceIndex = await getPriceIndex();
  return (
    <Container className="py-6 lg:py-10">
      <Breadcrumbs items={[{ label: "Cart", href: "/cart" }, { label: "Checkout" }]} />
      <h1 className="mb-6 mt-4 font-display text-3xl font-extrabold sm:text-4xl">Checkout</h1>
      <CheckoutView priceIndex={priceIndex} />
    </Container>
  );
}
