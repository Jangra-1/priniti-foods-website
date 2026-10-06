import type { Metadata } from "next";
import { AuthShell } from "@/components/account/AuthShell";
import { SignupForm } from "@/components/account/SignupForm";
import { merchandising } from "@/data/merchandising";
import { getProductsBySlugs } from "@/lib/api/products";

const description = "Create a Priniti Foods account to make your shopping for snacks, sweets and bakery products easier.";

export const metadata: Metadata = {
  title: { absolute: "Create Account | Priniti Foods" },
  description,
  alternates: { canonical: "/signup" },
  robots: { index: false, follow: false },
  openGraph: { title: "Create Account | Priniti Foods", description, url: "/signup" },
};

export default async function SignupPage() {
  const packs = await getProductsBySlugs([...merchandising.heroSlugs]);
  return (
    <AuthShell
      title="Create Your Account"
      subtitle="Create an account to make your Priniti Foods shopping experience easier."
      panelTitle="Snacks for every moment."
      panelText="Join Priniti Foods and explore namkeen, chips, puffs, sweets, cookies and more."
      products={packs}
    >
      <SignupForm />
    </AuthShell>
  );
}
