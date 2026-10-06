import type { Metadata } from "next";
import { PolicyPage } from "@/components/legal/PolicyPage";
import { shippingPolicy } from "@/data/legal";

export const metadata: Metadata = {
  title: { absolute: shippingPolicy.metaTitle },
  description: shippingPolicy.description,
  alternates: { canonical: "/shipping-policy" },
  openGraph: { title: shippingPolicy.metaTitle, description: shippingPolicy.description, url: "/shipping-policy" },
};

export default function Page() {
  return <PolicyPage policy={shippingPolicy} />;
}
