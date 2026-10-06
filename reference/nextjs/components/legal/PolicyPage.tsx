import { Headphones, Info, Mail, MapPin, Phone } from "lucide-react";
import Link from "next/link";
import { Container } from "@/components/layout/Container";
import { PageHeader } from "@/components/layout/PageHeader";
import { company } from "@/data/company";
import { policyMeta, type Policy, type PolicySection } from "@/data/legal";
import { cn } from "@/lib/cn";

const related = [
  { href: "/privacy-policy", label: "Privacy Policy" },
  { href: "/terms", label: "Terms & Conditions" },
  { href: "/shipping-policy", label: "Shipping Policy" },
  { href: "/return-policy", label: "Returns & Refund Policy" },
] as const;

function PendingNote({ children }: { children: string }) {
  return (
    <p className="flex items-start gap-2.5 rounded-xl border-l-4 border-lime bg-lime-tint px-4 py-3 text-sm leading-relaxed text-ink">
      <Info className="mt-0.5 size-4 shrink-0 text-ink-soft" aria-hidden />
      <span>
        <strong className="font-semibold">{policyMeta.pendingLabel}.</strong> {children}
      </span>
    </p>
  );
}

function Prose({ paragraphs, bullets }: { paragraphs?: string[]; bullets?: string[] }) {
  return (
    <>
      {paragraphs?.map((p) => (
        <p key={p} className="text-[15px] leading-relaxed text-ink-soft">
          {p}
        </p>
      ))}
      {bullets?.length ? (
        <ul role="list" className="flex flex-col gap-1.5 text-[15px] leading-relaxed text-ink-soft">
          {bullets.map((b) => (
            <li key={b} className="flex gap-2.5">
              <span aria-hidden className="mt-2 size-1.5 shrink-0 rounded-full bg-brand" />
              <span>{b}</span>
            </li>
          ))}
        </ul>
      ) : null}
    </>
  );
}

function Section({ section, index }: { section: PolicySection; index: number }) {
  return (
    <section id={section.id} aria-labelledby={`${section.id}-title`} className="scroll-mt-24 rounded-2xl bg-surface p-5 shadow-card ring-1 ring-line/70 sm:p-6">
      <h2 id={`${section.id}-title`} className="flex items-baseline gap-2.5 font-display text-lg font-extrabold tracking-tight sm:text-xl">
        <span className="text-sm font-bold text-brand">{String(index + 1).padStart(2, "0")}</span>
        {section.title}
      </h2>
      <div className="mt-3 flex flex-col gap-3">
        <Prose paragraphs={section.paragraphs} bullets={section.bullets} />
        {section.subsections?.map((s) => (
          <div key={s.title} className="flex flex-col gap-2 rounded-xl bg-canvas p-4">
            <h3 className="font-display text-base font-bold">{s.title}</h3>
            <Prose paragraphs={s.paragraphs} bullets={s.bullets} />
          </div>
        ))}
        {section.pending ? <PendingNote>{section.pending}</PendingNote> : null}
      </div>
    </section>
  );
}

/** Shared layout for the four policy pages: compact header, optional table of contents, sections, contact card. */
export function PolicyPage({ policy }: { policy: Policy }) {
  const care = company.contact.customerCare;
  const toc = (
    <ol className="flex flex-col gap-1 text-sm">
      {policy.sections.map((s, i) => (
        <li key={s.id}>
          <a href={`#${s.id}`} className="flex gap-2 rounded-lg px-2.5 py-1.5 text-ink-soft transition-colors hover:bg-brand-tint hover:text-brand">
            <span className="font-bold text-brand/80">{String(i + 1).padStart(2, "0")}</span>
            {s.title}
          </a>
        </li>
      ))}
    </ol>
  );

  return (
    <>
      <PageHeader eyebrow="Priniti Foods" title={policy.title} description={policy.summary}>
        <ul role="list" className="flex flex-wrap gap-2 text-xs font-semibold">
          <li className="rounded-full bg-lime-tint px-3 py-1 text-ink">{policyMeta.draftNote}</li>
          <li className="rounded-full bg-surface px-3 py-1 text-ink-soft ring-1 ring-line">Last updated: {policyMeta.lastUpdated}</li>
          <li className="rounded-full bg-surface px-3 py-1 text-ink-soft ring-1 ring-line">{policy.status}</li>
        </ul>
      </PageHeader>

      <section className="bg-canvas py-6 lg:py-10">
        <Container className="grid gap-5 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-8">
          <aside className="lg:sticky lg:top-24 lg:self-start">
            <nav aria-label={`${policy.title}: on this page`}>
              <details className="rounded-2xl bg-surface p-3 shadow-card ring-1 ring-line/70 lg:hidden">
                <summary className="cursor-pointer list-none px-2 py-1 font-display text-sm font-bold [&::-webkit-details-marker]:hidden">On this page</summary>
                <div className="mt-2">{toc}</div>
              </details>
              <div className="hidden rounded-2xl bg-surface p-3 shadow-card ring-1 ring-line/70 lg:block">
                <p className="px-2.5 pb-2 text-[11px] font-bold uppercase tracking-wide text-ink-soft">On this page</p>
                {toc}
              </div>
            </nav>
          </aside>

          <div className="flex min-w-0 flex-col gap-3 lg:gap-4">
            {policy.sections.map((s, i) => (
              <Section key={s.id} section={s} index={i} />
            ))}

            <section aria-labelledby="policy-contact-title" className="relative overflow-hidden rounded-2xl bg-surface p-5 shadow-card ring-1 ring-line/70 sm:p-6">
              <span aria-hidden className="absolute inset-x-0 top-0 h-1 bg-brand" />
              <div className="flex items-center gap-3">
                <span className="flex size-10 items-center justify-center rounded-xl bg-brand-tint text-brand">
                  <Headphones className="size-5" aria-hidden />
                </span>
                <div>
                  <h2 id="policy-contact-title" className="font-display text-lg font-extrabold leading-tight">
                    Questions? Contact Customer Care
                  </h2>
                  <p className="text-xs text-ink-soft">{company.legalName} · Customer Care / Feedback / Consumer Complaint</p>
                </div>
              </div>
              <div className="mt-4 flex flex-col gap-2 text-sm font-semibold sm:flex-row sm:flex-wrap sm:gap-x-6">
                <a href={`tel:${care.tel}`} className="inline-flex items-center gap-2 transition-colors hover:text-brand">
                  <Phone className="size-4 text-brand" aria-hidden /> {care.phone}
                </a>
                <a href={`mailto:${care.email}`} className="inline-flex items-center gap-2 break-all transition-colors hover:text-brand">
                  <Mail className="size-4 shrink-0 text-brand" aria-hidden /> {care.email}
                </a>
              </div>
              <ul role="list" aria-label="Manufacturing units" className="mt-4 grid gap-3 border-t border-line pt-4 sm:grid-cols-2">
                {company.units.map((u) => (
                  <li key={u.name} className="flex gap-2.5 text-xs leading-relaxed text-ink-soft">
                    <MapPin className="mt-0.5 size-4 shrink-0 text-brand" aria-hidden />
                    <address className="not-italic">
                      <span className="block font-bold text-ink">{u.name} (manufacturing unit)</span>
                      {u.lines.map((l) => (
                        <span key={l} className="block">
                          {l}
                        </span>
                      ))}
                    </address>
                  </li>
                ))}
              </ul>
              <p className="mt-4 text-xs text-ink-soft">
                More ways to reach us are on the{" "}
                <Link href="/contact" className="font-semibold text-brand underline-offset-4 hover:underline">
                  Contact page
                </Link>
                .
              </p>
            </section>

            <nav aria-label="Other policies" className="rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line/70">
              <p className="text-[11px] font-bold uppercase tracking-wide text-ink-soft">Other policies</p>
              <ul role="list" className="mt-2 flex flex-wrap gap-2">
                {related
                  .filter((r) => r.href !== `/${policy.slug}`)
                  .map((r) => (
                    <li key={r.href}>
                      <Link
                        href={r.href}
                        className={cn("inline-flex min-h-9 items-center rounded-full bg-canvas px-3.5 text-sm font-semibold ring-1 ring-line transition-colors hover:text-brand hover:ring-brand/50")}
                      >
                        {r.label}
                      </Link>
                    </li>
                  ))}
              </ul>
            </nav>
          </div>
        </Container>
      </section>
    </>
  );
}
