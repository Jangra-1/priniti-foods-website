import type { Metadata } from "next";
import { ArrowRight } from "lucide-react";
import { ContactCards, LocationCards, ReachPanel } from "@/components/contact/ContactCards";
import { ContactForm } from "@/components/contact/ContactForm";
import { Container } from "@/components/layout/Container";
import { JsonLd } from "@/components/layout/JsonLd";
import { PackFan } from "@/components/sections/PackFan";
import { ButtonLink } from "@/components/ui/Button";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { company } from "@/data/company";
import { merchandising } from "@/data/merchandising";
import { siteConfig } from "@/data/site";
import { getProductsBySlugs } from "@/lib/api/products";

const description =
  "Contact Priniti Foods for customer care and feedback, sales and export enquiries. Phone and email details, plus our manufacturing units in Sonipat, Haryana and Kanpur Dehat, Uttar Pradesh.";

export const metadata: Metadata = {
  title: { absolute: "Contact Priniti Foods | Get in Touch" },
  description,
  alternates: { canonical: "/contact" },
  openGraph: { title: "Contact Priniti Foods | Get in Touch", description, url: "/contact" },
};

const section = "py-8 lg:py-10";

/** Verified contact details only (data/company.ts, from the official Contact Us page). */
export default async function ContactPage() {
  const packs = await getProductsBySlugs([...merchandising.heroSlugs]);
  const { customerCare, sales, export: exp } = company.contact;
  const jsonLd = {
    "@context": "https://schema.org",
    "@type": "Organization",
    name: company.legalName,
    url: siteConfig.url,
    contactPoint: [
      { "@type": "ContactPoint", contactType: "customer service", telephone: customerCare.tel, email: customerCare.email },
      { "@type": "ContactPoint", contactType: "sales", telephone: sales.tel },
      { "@type": "ContactPoint", contactType: "export enquiries", telephone: exp.tel, email: exp.email },
    ],
    location: [
      {
        "@type": "Place",
        name: "Priniti Foods Unit 1",
        address: { "@type": "PostalAddress", streetAddress: "Khasra No. 28/7/1, VPO Nathupur", addressLocality: "Sonipat", addressRegion: "Haryana", postalCode: "131029", addressCountry: "IN" },
      },
      {
        "@type": "Place",
        name: "Priniti Foods Unit 2",
        address: { "@type": "PostalAddress", streetAddress: "Plot No. G-44, Jainpur Industrial Area", addressLocality: "Kanpur Dehat", addressRegion: "Uttar Pradesh", postalCode: "209311", addressCountry: "IN" },
      },
    ],
  };

  return (
    <>
      <JsonLd data={jsonLd} />

      {/* 1. Hero */}
      <section className="relative overflow-hidden bg-linear-to-br from-brand-tint via-blush to-surface">
        <Container className="grid items-center gap-6 py-8 lg:grid-cols-[1fr_1.05fr] lg:gap-8 lg:py-10">
          <div className="max-w-xl">
            <Eyebrow className="mb-3">Contact Priniti Foods</Eyebrow>
            <h1 className="font-display text-[2.25rem] font-extrabold leading-[1.05] tracking-tight sm:text-5xl xl:text-[3.25rem]">
              Let&apos;s <span className="text-brand">Talk</span>
            </h1>
            <p className="mt-3 max-w-md text-base leading-relaxed text-ink-soft sm:text-lg">
              Get in touch with Priniti Foods for general enquiries, product enquiries, sales and export enquiries.
            </p>
            <div className="mt-5 flex flex-col gap-3 sm:flex-row">
              <ButtonLink href="/shop" size="md" className="h-12 px-6 text-[15px]">
                Explore Products
                <ArrowRight className="size-4" aria-hidden />
              </ButtonLink>
              <ButtonLink href="#enquiry" variant="outline" size="md" className="h-12 px-6 text-[15px]">
                Send an Enquiry
              </ButtonLink>
            </div>
          </div>
          <div className="relative isolate aspect-[16/10] overflow-hidden rounded-3xl bg-navy">
            <div aria-hidden className="absolute -right-[8%] -top-[22%] -z-10 size-[62%] rounded-full bg-navy-dark" />
            <div aria-hidden className="absolute -bottom-[30%] -left-[8%] -z-10 size-[58%] rounded-full bg-brand" />
            <PackFan products={packs} priority className="absolute -inset-x-[2%] bottom-[8%] top-[14%] size-auto" />
          </div>
        </Container>
      </section>

      {/* 2. Contact options */}
      <section aria-labelledby="options-heading" className={`bg-surface ${section}`}>
        <Container>
          <SectionHeading id="options-heading" eyebrow="How can we help?" title="Choose how to reach us" align="center" className="mb-5" />
          <ContactCards />
        </Container>
      </section>

      {/* 3. Form + company information */}
      <section id="enquiry" aria-labelledby="form-heading" className={`scroll-mt-20 bg-canvas ${section}`}>
        <Container>
          <h2 id="form-heading" className="sr-only">
            Send an enquiry
          </h2>
          <div className="grid gap-4 lg:grid-cols-[1.35fr_1fr] lg:gap-6">
            <ContactForm />
            <ReachPanel />
          </div>
        </Container>
      </section>

      {/* 4. Our locations */}
      <section id="locations" aria-labelledby="locations-heading" className={`scroll-mt-20 bg-surface ${section}`}>
        <Container>
          <SectionHeading
            id="locations-heading"
            eyebrow="Visit us"
            title="Our Locations"
            description="Priniti Foods operates from manufacturing facilities in Sonipat, Haryana and Kanpur, Uttar Pradesh."
            align="center"
            className="mb-5"
          />
          <LocationCards />
        </Container>
      </section>

      {/* 5. Final CTA */}
      <section className="bg-canvas py-6 lg:py-8">
        <Container>
          <div className="relative isolate flex flex-col items-start justify-between gap-5 overflow-hidden rounded-3xl bg-brand px-6 py-8 text-white sm:flex-row sm:items-center sm:px-10">
            <div aria-hidden className="absolute -right-10 -top-16 -z-10 size-52 rounded-full bg-navy" />
            <div>
              <h2 className="font-display text-2xl font-extrabold sm:text-3xl">Looking for Priniti Foods?</h2>
              <p className="mt-1 text-sm text-white/90">Explore our range of snacks, sweets and bakery products.</p>
            </div>
            <div className="flex flex-col gap-3 sm:flex-row">
              <ButtonLink href="/shop" variant="light" size="md" className="h-12 px-6">
                Explore Products
              </ButtonLink>
              <ButtonLink href="/track-order" variant="outline" size="md" className="h-12 border-white px-6 text-white hover:bg-white hover:text-brand">
                Track Order
              </ButtonLink>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
