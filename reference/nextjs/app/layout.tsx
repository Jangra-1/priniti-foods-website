import type { Metadata, Viewport } from "next";
import { Inter, Poppins } from "next/font/google";
import { CartDrawer } from "@/components/commerce/CartDrawer";
import { QuickViewModal } from "@/components/commerce/QuickViewModal";
import { AnnouncementBar } from "@/components/layout/AnnouncementBar";
import { TestPriceBanner } from "@/components/layout/TestPriceBanner";
import { Footer } from "@/components/layout/Footer";
import { Header } from "@/components/layout/Header";
import { Toaster } from "@/components/ui/Toaster";
import { siteConfig } from "@/data/site";
import { getCategories } from "@/lib/api/categories";
import { getSearchIndex } from "@/lib/api/products";
import "./globals.css";

const inter = Inter({ subsets: ["latin"], variable: "--font-inter", display: "swap" });
const poppins = Poppins({ subsets: ["latin"], weight: ["500", "600", "700", "800"], variable: "--font-poppins", display: "swap" });

export const metadata: Metadata = {
  metadataBase: new URL(siteConfig.url),
  title: { default: `${siteConfig.name} | Snacks for every moment`, template: `%s | ${siteConfig.name}` },
  description: siteConfig.description,
  openGraph: { type: "website", siteName: siteConfig.name, locale: "en_IN", title: siteConfig.name, description: siteConfig.description },
  // Keep the demo out of search engines until real catalog data is live. Remove at launch.
  robots: { index: false, follow: false },
};

export const viewport: Viewport = { themeColor: "#f7f8fb" };

export default async function RootLayout({ children }: { children: React.ReactNode }) {
  const [categories, searchIndex] = await Promise.all([getCategories(), getSearchIndex()]);
  return (
    <html lang="en-IN" className={`${inter.variable} ${poppins.variable}`}>
      <body className="min-h-dvh">
        <a
          href="#main"
          className="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-full focus:bg-ink focus:px-4 focus:py-2 focus:text-white"
        >
          Skip to content
        </a>
        <TestPriceBanner />
        <AnnouncementBar />
        <Header categories={categories} searchIndex={searchIndex} />
        <main id="main">{children}</main>
        <Footer />
        <CartDrawer />
        <QuickViewModal />
        <Toaster />
      </body>
    </html>
  );
}
