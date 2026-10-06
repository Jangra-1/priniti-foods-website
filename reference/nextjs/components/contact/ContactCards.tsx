import { ArrowUpRight, Globe, Headphones, Mail, MapPin, Phone } from "lucide-react";
import type { ReactNode } from "react";
import { company } from "@/data/company";
import { cn } from "@/lib/cn";

const row =
  "group inline-flex w-full items-center gap-2.5 rounded-xl bg-canvas px-3 py-2 text-sm font-semibold text-ink transition-colors hover:bg-brand-tint hover:text-brand";

function Row({ href, icon, children }: { href: string; icon: ReactNode; children: ReactNode }) {
  return (
    <a href={href} className={row}>
      <span className="text-brand">{icon}</span>
      <span className="min-w-0 break-words">{children}</span>
    </a>
  );
}

function OptionCard({
  accent,
  tint,
  icon,
  title,
  tag,
  children,
}: {
  accent: string;
  tint: string;
  icon: ReactNode;
  title: string;
  tag: string;
  children: ReactNode;
}) {
  return (
    <li className="relative overflow-hidden rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line/70 transition-shadow hover:shadow-soft">
      <span aria-hidden className={cn("absolute inset-x-0 top-0 h-1", accent)} />
      <div className="flex items-center gap-3">
        <span className={cn("flex size-10 shrink-0 items-center justify-center rounded-xl", tint)}>{icon}</span>
        <div className="min-w-0">
          <h3 className="font-display text-base font-bold leading-tight">{title}</h3>
          <p className="mt-0.5 text-[11px] font-bold uppercase tracking-wide text-ink-soft">{tag}</p>
        </div>
      </div>
      <div className="mt-3 flex flex-col gap-1.5">{children}</div>
    </li>
  );
}

/** Verified phone numbers and emails only (data/company.ts). */
export function ContactCards() {
  const { customerCare, sales, export: exp } = company.contact;
  return (
    <ul role="list" className="grid gap-3 md:grid-cols-3 md:gap-4">
      <OptionCard accent="bg-brand" tint="bg-brand-tint text-brand" icon={<Headphones className="size-5" aria-hidden />} title="Customer Care" tag="Feedback / Consumer Complaint">
        <Row href={`tel:${customerCare.tel}`} icon={<Phone className="size-4" aria-hidden />}>
          {customerCare.phone}
        </Row>
        <Row href={`mailto:${customerCare.email}`} icon={<Mail className="size-4" aria-hidden />}>
          {customerCare.email}
        </Row>
      </OptionCard>
      <OptionCard accent="bg-navy" tint="bg-navy-tint text-navy" icon={<Phone className="size-5" aria-hidden />} title="Sales Department" tag="Sales">
        <Row href={`tel:${sales.tel}`} icon={<Phone className="size-4" aria-hidden />}>
          {sales.phone}
        </Row>
      </OptionCard>
      <OptionCard accent="bg-leaf" tint="bg-leaf-tint text-leaf" icon={<Globe className="size-5" aria-hidden />} title="Export Enquiry" tag="Export">
        <Row href={`tel:${exp.tel}`} icon={<Phone className="size-4" aria-hidden />}>
          {exp.phone}
        </Row>
        <Row href={`mailto:${exp.email}`} icon={<Mail className="size-4" aria-hidden />}>
          {exp.email}
        </Row>
      </OptionCard>
    </ul>
  );
}

/** Compact "Reach Priniti Foods" panel shown beside the form. */
export function ReachPanel() {
  const { customerCare, sales, export: exp } = company.contact;
  const link = "inline-flex items-center gap-2 text-sm font-semibold text-white transition-colors hover:text-lime";
  return (
    <div className="relative isolate h-full overflow-hidden rounded-3xl bg-navy p-5 text-white shadow-soft sm:p-6">
      <div aria-hidden className="absolute -right-16 -top-16 -z-10 size-56 rounded-full bg-navy-dark" />
      <div aria-hidden className="absolute -bottom-20 -left-10 -z-10 size-52 rounded-full bg-brand/80" />
      <h2 className="font-display text-xl font-extrabold">Reach Priniti Foods</h2>
      <p className="mt-1 text-sm text-white/75">Call or email the right team directly.</p>
      <dl className="mt-5 flex flex-col gap-4">
        <div>
          <dt className="text-[11px] font-bold uppercase tracking-wide text-lime">Customer Care</dt>
          <dd className="mt-1.5 flex flex-col gap-1">
            <a href={`tel:${customerCare.tel}`} className={link}>
              <Phone className="size-4 shrink-0" aria-hidden /> {customerCare.phone}
            </a>
            <a href={`mailto:${customerCare.email}`} className={link}>
              <Mail className="size-4 shrink-0" aria-hidden /> {customerCare.email}
            </a>
          </dd>
        </div>
        <div>
          <dt className="text-[11px] font-bold uppercase tracking-wide text-lime">Sales</dt>
          <dd className="mt-1.5">
            <a href={`tel:${sales.tel}`} className={link}>
              <Phone className="size-4 shrink-0" aria-hidden /> {sales.phone}
            </a>
          </dd>
        </div>
        <div>
          <dt className="text-[11px] font-bold uppercase tracking-wide text-lime">Export</dt>
          <dd className="mt-1.5 flex flex-col gap-1">
            <a href={`tel:${exp.tel}`} className={link}>
              <Phone className="size-4 shrink-0" aria-hidden /> {exp.phone}
            </a>
            <a href={`mailto:${exp.email}`} className={`${link} break-all`}>
              <Mail className="size-4 shrink-0" aria-hidden /> {exp.email}
            </a>
          </dd>
        </div>
      </dl>
    </div>
  );
}

const mapSearch = (lines: readonly string[]) => `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(lines.join(", "))}`;

/** Two manufacturing units with their verified addresses. "View Location" is a map SEARCH on the address (no coordinates claimed). */
export function LocationCards() {
  return (
    <ul role="list" className="grid gap-3 md:grid-cols-2 md:gap-4">
      {company.units.map((u, i) => {
        const [city, state] = u.place.split(", ");
        return (
          <li
            key={u.name}
            className={cn("relative isolate overflow-hidden rounded-3xl p-6 text-white shadow-soft sm:p-7", i === 0 ? "bg-navy" : "bg-brand")}
          >
            <div aria-hidden className={cn("absolute -right-14 -top-14 -z-10 size-52 rounded-full", i === 0 ? "bg-navy-dark" : "bg-brand-dark/70")} />
            <span aria-hidden className="pointer-events-none absolute -bottom-3 right-3 -z-10 select-none font-display text-6xl font-extrabold uppercase tracking-tight text-white/10 sm:text-7xl">
              {city}
            </span>
            <p className="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-wide">
              <MapPin className="size-3.5" aria-hidden /> {u.name} · Manufacturing unit
            </p>
            <h3 className="mt-3 font-display text-3xl font-extrabold leading-tight">{city}</h3>
            <p className="text-sm font-semibold text-white/80">{state}</p>
            <address className="mt-3 text-sm not-italic leading-relaxed text-white/90">
              {u.lines.map((l) => (
                <span key={l} className="block">
                  {l}
                </span>
              ))}
            </address>
            <a
              href={mapSearch(u.lines)}
              target="_blank"
              rel="noopener noreferrer"
              className="mt-5 inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-lime"
            >
              View Location
              <ArrowUpRight className="size-4" aria-hidden />
              <span className="sr-only">for {u.name} on a map (opens in a new tab)</span>
            </a>
          </li>
        );
      })}
    </ul>
  );
}
