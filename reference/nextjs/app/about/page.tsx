import type { Metadata } from "next";
import Image from "next/image";
import { ArrowRight, Factory, MapPin, ShieldCheck, Users, Warehouse } from "lucide-react";
import { JourneyTimeline } from "@/components/about/JourneyTimeline";
import { CategoryCard } from "@/components/catalog/CategoryCard";
import { Container } from "@/components/layout/Container";
import { PackFan } from "@/components/sections/PackFan";
import { ButtonLink } from "@/components/ui/Button";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { company } from "@/data/company";
import { merchandising } from "@/data/merchandising";
import { getCategories } from "@/lib/api/categories";
import { getCategoryProductCounts, getProductsBySlugs } from "@/lib/api/products";

const description =
  "Priniti Foods Pvt. Ltd., founded in 2009, makes namkeen, chips, puffs, sweets, cookies, rusk and more at its units in Sonipat, Haryana and Kanpur, Uttar Pradesh.";

export const metadata: Metadata = {
  title: "About Priniti Foods",
  description,
  alternates: { canonical: "/about" },
  openGraph: { title: "About Priniti Foods", description, url: "/about", images: [company.images.facility.src] },
};

const card = "rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line/70";
const section = "py-8 lg:py-10";
const img = company.images;

/**
 * Verified facts and real photographs only (data/company.ts, public/images/about).
 * Valued-partner logos are intentionally NOT shown: no verified logo assets exist in the project.
 */
export default async function AboutPage() {
  const [packs, categories, counts] = await Promise.all([
    getProductsBySlugs([...merchandising.heroSlugs]),
    getCategories(),
    getCategoryProductCounts(),
  ]);
  const onlineCount = Object.values(counts).reduce((a, b) => a + b, 0);

  return (
    <>
      {/* 1. Hero */}
      <section className="relative overflow-hidden bg-linear-to-br from-brand-tint via-blush to-surface">
        <Container className="grid items-center gap-6 py-8 lg:grid-cols-[1fr_1.05fr] lg:gap-8 lg:py-10">
          <div className="max-w-xl">
            <Eyebrow className="mb-3">About Priniti Foods</Eyebrow>
            <h1 className="font-display text-[2.25rem] font-extrabold leading-[1.05] tracking-tight sm:text-5xl xl:text-[3.25rem]">
              <span className="block">Good Food. Great Journey.</span>
              <span className="block text-brand">Since {company.founded}.</span>
            </h1>
            <p className="mt-3 max-w-md text-base leading-relaxed text-ink-soft sm:text-lg">
              {company.legalName} is an Indian snacks and FMCG business, making namkeen, chips, puffs, sweets, cookies, rusk and more. Swad Mein No.1.
            </p>
            <div className="mt-5 flex flex-col gap-3 sm:flex-row">
              <ButtonLink href="/shop" size="md" className="h-12 px-6 text-[15px]">
                Explore Products
                <ArrowRight className="size-4" aria-hidden />
              </ButtonLink>
              <ButtonLink href="/contact" variant="outline" size="md" className="h-12 px-6 text-[15px]">
                Contact Us
              </ButtonLink>
            </div>
          </div>
          <div className="relative isolate aspect-[16/10] overflow-hidden rounded-3xl bg-navy">
            <div aria-hidden className="absolute -right-[8%] -top-[22%] -z-10 size-[62%] rounded-full bg-navy-dark" />
            <div aria-hidden className="absolute -bottom-[30%] -left-[8%] -z-10 size-[58%] rounded-full bg-brand" />
            <PackFan products={packs} priority className="absolute -inset-x-[2%] bottom-[8%] top-[14%] size-auto" />
            <p className="absolute left-4 top-4 rounded-xl bg-white px-3 py-1.5 font-display text-xs font-bold text-brand shadow-lift">Since {company.founded}</p>
          </div>
        </Container>
      </section>

      {/* 2. Who we are */}
      <section aria-labelledby="who-heading" className={`bg-surface ${section}`}>
        <Container className="grid items-center gap-6 lg:grid-cols-[1fr_1.1fr] lg:gap-10">
          <div className="relative aspect-[4/3] overflow-hidden rounded-3xl bg-navy-tint shadow-soft ring-1 ring-line/70">
            <Image src={img.facility.src} alt={img.facility.alt} fill sizes="(min-width:1024px) 45vw, 94vw" className="object-cover" />
            <p className="absolute bottom-3 left-3 rounded-xl bg-white/95 px-3 py-1.5 text-xs font-bold text-ink shadow-card">Our facility</p>
          </div>
          <div>
            <Eyebrow className="mb-3">Who we are</Eyebrow>
            <h2 id="who-heading" className="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
              Snacks made for every moment
            </h2>
            <div className="mt-3 flex flex-col gap-3 text-[15px] leading-relaxed text-ink-soft">
              {company.intro.map((p) => (
                <p key={p}>{p}</p>
              ))}
            </div>
            <ul role="list" aria-label="Core values" className="mt-4 flex flex-wrap gap-2">
              {company.values.map((v) => (
                <li key={v} className="rounded-full bg-brand-tint px-3.5 py-1.5 text-xs font-bold uppercase tracking-wide text-brand">
                  {v}
                </li>
              ))}
            </ul>
            <div className="mt-5 flex items-center gap-4 rounded-2xl bg-canvas p-3 ring-1 ring-line/70">
              <div className="relative size-20 shrink-0 overflow-hidden rounded-xl bg-white">
                <Image src={img.managingDirector.src} alt={img.managingDirector.alt} fill sizes="80px" className="object-cover object-top" />
              </div>
              <div>
                <p className="font-display text-lg font-bold leading-tight">{company.managingDirector.name}</p>
                <p className="text-sm font-semibold text-brand">{company.managingDirector.title}</p>
                <p className="mt-0.5 text-sm text-ink-soft">{company.managingDirector.experience}</p>
              </div>
            </div>
          </div>
        </Container>
      </section>

      {/* 3. Our reach: strong stats band */}
      <section aria-labelledby="reach-heading" className="relative isolate overflow-hidden bg-brand py-9 text-white lg:py-11">
        <div aria-hidden className="absolute -right-16 -top-24 -z-10 size-72 rounded-full bg-navy" />
        <div aria-hidden className="absolute -bottom-28 left-10 -z-10 size-60 rounded-full bg-brand-dark/60" />
        <Container>
          <div className="text-center">
            <p className="text-[11px] font-bold uppercase tracking-[0.18em] text-white/80">Our reach</p>
            <h2 id="reach-heading" className="mt-1 font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
              The scale of Priniti Foods
            </h2>
          </div>
          <dl className="mt-6 grid grid-cols-2 gap-x-4 gap-y-6 sm:grid-cols-3 lg:grid-cols-6">
            {company.reach.map((r) => (
              <div key={r.label} className="text-center">
                <dd className="font-display text-3xl font-extrabold tracking-tight sm:text-4xl">{r.value}</dd>
                <span aria-hidden className="mx-auto my-1.5 block h-1 w-8 rounded-full bg-white/50" />
                <dt className="text-[10px] font-bold uppercase tracking-[0.14em] text-white/85 sm:text-xs">{r.label}</dt>
              </div>
            ))}
          </dl>
        </Container>
      </section>

      {/* 4. Our journey */}
      <section aria-labelledby="journey-heading" className={`bg-surface ${section}`}>
        <Container>
          <SectionHeading id="journey-heading" eyebrow="Our journey" title="From 2009 to today" align="center" className="mb-6" />
          <JourneyTimeline />
        </Container>
      </section>

      {/* 5. Manufacturing strength */}
      <section aria-labelledby="mfg-heading" className={`bg-canvas ${section}`}>
        <Container>
          <SectionHeading
            id="mfg-heading"
            eyebrow="Manufacturing strength"
            title="Two advanced units, one standard"
            description="Two manufacturing units in Sonipat, Haryana and Kanpur, Uttar Pradesh."
            align="center"
            className="mb-6"
          />
          <div className="grid gap-3 md:grid-cols-2 md:gap-4">
            {[img.facility, img.production].map((p, i) => (
              <figure key={p.src} className="relative aspect-[16/9] overflow-hidden rounded-3xl bg-navy-tint shadow-soft ring-1 ring-line/70">
                <Image src={p.src} alt={p.alt} fill sizes="(min-width:768px) 48vw, 94vw" className="object-cover" />
                <figcaption className="absolute inset-x-0 bottom-0 bg-linear-to-t from-ink/80 to-transparent px-4 pb-3 pt-10 text-sm font-semibold text-white">
                  {i === 0 ? "Our facility" : "Packaging and production"}
                </figcaption>
              </figure>
            ))}
          </div>
          <div className="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 md:mt-4 md:gap-4">
            {company.units.map((u, i) => (
              <div key={u.name} className={card}>
                <span className="flex size-10 items-center justify-center rounded-xl bg-brand-tint text-brand">
                  <MapPin className="size-5" aria-hidden />
                </span>
                <p className="mt-3 text-[11px] font-bold uppercase tracking-wide text-brand">{u.name}</p>
                <p className="font-display text-lg font-semibold leading-tight">{u.place}</p>
                {i === 1 ? <p className="mt-1 text-xs font-semibold text-navy">Established 2022 as our second manufacturing unit</p> : null}
                <address className="mt-1.5 text-xs not-italic leading-relaxed text-ink-soft">
                  {u.lines.map((l) => (
                    <span key={l} className="block">
                      {l}
                    </span>
                  ))}
                </address>
              </div>
            ))}
            <div className={card}>
              <span className="flex size-10 items-center justify-center rounded-xl bg-navy-tint text-navy">
                <Factory className="size-5" aria-hidden />
              </span>
              <p className="mt-3 text-[11px] font-bold uppercase tracking-wide text-ink-soft">Manufacturing capacity</p>
              <p className="font-display text-2xl font-extrabold leading-tight text-navy">{company.manufacturing.capacity}</p>
            </div>
            <div className={card}>
              <span className="flex size-10 items-center justify-center rounded-xl bg-navy-tint text-navy">
                <Warehouse className="size-5" aria-hidden />
              </span>
              <p className="mt-3 text-[11px] font-bold uppercase tracking-wide text-ink-soft">Warehouse availability</p>
              <p className="font-display text-2xl font-extrabold leading-tight text-navy">{company.manufacturing.warehouse}</p>
              <p className="mt-1 text-xs text-ink-soft">Ready to be shipped to our valued customers.</p>
            </div>
          </div>
        </Container>
      </section>

      {/* 6. Product range: the real, linked categories */}
      <section aria-labelledby="range-heading" className={`bg-surface ${section}`}>
        <Container>
          <SectionHeading
            id="range-heading"
            eyebrow="Our product range"
            title={`${company.range.categories} categories, ${company.range.skus} SKUs`}
            description={`${onlineCount} products are available to browse in this online store today.`}
            href="/shop"
            linkLabel="Shop all"
            className="mb-5"
          />
          <ul
            role="list"
            className="scrollbar-none -mx-4 flex snap-x snap-mandatory gap-2.5 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:px-6 md:mx-0 md:grid md:grid-cols-5 md:gap-3 md:overflow-visible md:px-0 md:pb-0 lg:grid-cols-10 lg:gap-2.5"
          >
            {categories.map((c, i) => (
              <li key={c.slug} className="w-[34%] shrink-0 snap-start sm:w-[24%] md:w-auto">
                <CategoryCard category={c} index={i} productCount={counts[c.slug]} />
              </li>
            ))}
          </ul>
        </Container>
      </section>

      {/* 7. Quality & certifications (the five officially listed) */}
      <section aria-labelledby="quality-heading" className="relative isolate overflow-hidden bg-navy py-10 text-white lg:py-12">
        <div
          aria-hidden
          className="absolute inset-0 -z-10 opacity-[0.12]"
          style={{ backgroundImage: "radial-gradient(circle at 1px 1px, #fff 1px, transparent 0)", backgroundSize: "22px 22px" }}
        />
        <div aria-hidden className="absolute -right-20 -top-24 -z-10 size-72 rounded-full bg-navy-dark" />
        <Container>
          <div className="flex flex-col items-center text-center">
            <Eyebrow className="mb-2 text-lime [&>span]:bg-lime/50">Quality &amp; certifications</Eyebrow>
            <h2 id="quality-heading" className="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
              Quality at Every Step
            </h2>
            <p className="mt-1.5 max-w-xl text-sm text-white/75">
              Quality, integrity and customer-centricity guide every decision. Certifications and approvals as listed by Priniti Foods.
            </p>
          </div>
          <ul role="list" className="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            {company.certifications.map((c) => (
              <li key={c} className="flex flex-col items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-3 py-4 text-center backdrop-blur-sm">
                <span className="flex size-10 items-center justify-center rounded-xl bg-lime/20 text-lime">
                  <ShieldCheck className="size-5" aria-hidden />
                </span>
                <span className="font-display text-sm font-bold">{c}</span>
              </li>
            ))}
          </ul>
        </Container>
      </section>

      {/* 8. Our team */}
      <section aria-labelledby="team-heading" className={`bg-surface ${section}`}>
        <Container>
          <div className="grid items-end gap-5 lg:grid-cols-[1fr_1.4fr] lg:gap-10">
            <div>
              <Eyebrow className="mb-3">Our team</Eyebrow>
              <h2 id="team-heading" className="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
                <span className="text-brand">{company.team.total}</span> professionals
              </h2>
              <p className="mt-2 text-[15px] leading-relaxed text-ink-soft">{company.team.description}</p>
            </div>
            <dl className="grid grid-cols-3 gap-2.5">
              {company.team.groups.map((g) => (
                <div key={g.label} className="rounded-2xl bg-brand px-3 py-4 text-center text-white shadow-card">
                  <dd className="font-display text-2xl font-extrabold sm:text-3xl">{g.value}</dd>
                  <dt className="mt-1 text-[10px] font-bold uppercase tracking-wide text-white/90 sm:text-xs">{g.label}</dt>
                </div>
              ))}
            </dl>
          </div>
          <div className="mt-5 grid gap-3 md:grid-cols-2 md:gap-4">
            {[img.team1, img.team2].map((p) => (
              <div key={p.src} className="relative aspect-[3/2] overflow-hidden rounded-3xl bg-navy-tint shadow-soft ring-1 ring-line/70">
                <Image src={p.src} alt={p.alt} fill sizes="(min-width:768px) 48vw, 94vw" className="object-cover" />
              </div>
            ))}
          </div>
          <p className="mt-3 flex items-center gap-2 text-xs text-ink-soft">
            <Users className="size-4 text-brand" aria-hidden /> {company.team.groups.map((g) => `${g.value} ${g.label.toLowerCase()}`).join(" · ")}
          </p>
        </Container>
      </section>

      {/* 10. Final CTA (9, Valued Partners, is omitted: no verified logo assets) */}
      <section className="bg-canvas py-6 lg:py-8">
        <Container>
          <div className="relative isolate flex flex-col items-start justify-between gap-5 overflow-hidden rounded-3xl bg-brand px-6 py-8 text-white sm:flex-row sm:items-center sm:px-10">
            <div aria-hidden className="absolute -right-10 -top-16 -z-10 size-52 rounded-full bg-navy" />
            <div>
              <h2 className="font-display text-2xl font-extrabold sm:text-3xl">Explore the Priniti Range</h2>
              <p className="mt-1 text-sm text-white/90">Discover snacks, sweets and bakery products from Priniti Foods.</p>
            </div>
            <div className="flex flex-col gap-3 sm:flex-row">
              <ButtonLink href="/shop" variant="light" size="md" className="h-12 px-6">
                Explore Products
              </ButtonLink>
              <ButtonLink href="/contact" variant="outline" size="md" className="h-12 border-white px-6 text-white hover:bg-white hover:text-brand">
                Contact Us
              </ButtonLink>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
