import type { Metadata } from "next";
import { PolicyPage } from "@/components/legal/PolicyPage";
import { privacyPolicy } from "@/data/legal";

export const metadata: Metadata = {
  title: { absolute: privacyPolicy.metaTitle },
  description: privacyPolicy.description,
  alternates: { canonical: "/privacy-policy" },
  openGraph: { title: privacyPolicy.metaTitle, description: privacyPolicy.description, url: "/privacy-policy" },
};

export default function Page() {
  return <PolicyPage policy={privacyPolicy} />;
}
