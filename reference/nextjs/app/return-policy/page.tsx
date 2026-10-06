import type { Metadata } from "next";
import { PolicyPage } from "@/components/legal/PolicyPage";
import { returnPolicy } from "@/data/legal";

export const metadata: Metadata = {
  title: { absolute: returnPolicy.metaTitle },
  description: returnPolicy.description,
  alternates: { canonical: "/return-policy" },
  openGraph: { title: returnPolicy.metaTitle, description: returnPolicy.description, url: "/return-policy" },
};

export default function Page() {
  return <PolicyPage policy={returnPolicy} />;
}
