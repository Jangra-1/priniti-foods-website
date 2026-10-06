import type { Metadata } from "next";
import { PolicyPage } from "@/components/legal/PolicyPage";
import { termsPolicy } from "@/data/legal";

export const metadata: Metadata = {
  title: { absolute: termsPolicy.metaTitle },
  description: termsPolicy.description,
  alternates: { canonical: "/terms" },
  openGraph: { title: termsPolicy.metaTitle, description: termsPolicy.description, url: "/terms" },
};

export default function Page() {
  return <PolicyPage policy={termsPolicy} />;
}
