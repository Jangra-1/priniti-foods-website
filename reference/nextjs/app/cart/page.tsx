import type { Metadata } from "next";
import { CartView } from "@/components/commerce/CartView";
import { Breadcrumbs } from "@/components/layout/Breadcrumbs";
import { Container } from "@/components/layout/Container";
import { getPriceIndex } from "@/lib/api/products";

export const metadata: Metadata = {
  title: "Your cart",
  robots: { index: false, follow: false },
};

export default async function CartPage() {
  const priceIndex = await getPriceIndex();
  return (
    <Container className="py-6 lg:py-10">
      <Breadcrumbs items={[{ label: "Cart" }]} />
      <h1 className="mb-6 mt-4 font-display text-3xl font-extrabold sm:text-4xl">Your cart</h1>
      <CartView priceIndex={priceIndex} />
    </Container>
  );
}
