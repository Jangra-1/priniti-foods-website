import type { Metadata } from "next";
import { AuthShell } from "@/components/account/AuthShell";
import { LoginForm } from "@/components/account/LoginForm";
import { merchandising } from "@/data/merchandising";
import { getProductsBySlugs } from "@/lib/api/products";

const description = "Log in to your Priniti Foods account to continue shopping for namkeen, chips, sweets, cookies and more.";

export const metadata: Metadata = {
  title: { absolute: "Login | Priniti Foods" },
  description,
  alternates: { canonical: "/login" },
  robots: { index: false, follow: false },
  openGraph: { title: "Login | Priniti Foods", description, url: "/login" },
};

export default async function LoginPage() {
  const packs = await getProductsBySlugs([...merchandising.heroSlugs]);
  return (
    <AuthShell
      title="Welcome Back"
      subtitle="Sign in to continue shopping with Priniti Foods."
      panelTitle="Your favourite snacks, one sign-in away."
      panelText="Swad Mein No.1. Namkeen, chips, puffs, sweets, cookies and more."
      products={packs}
    >
      <LoginForm />
    </AuthShell>
  );
}
