import { Headphones, Mail, Phone } from "lucide-react";
import { ButtonLink } from "@/components/ui/Button";
import { company } from "@/data/company";

/** Customer care (verified details from data/company.ts) and a way back to shopping. */
export function TrackHelp() {
  const care = company.contact.customerCare;
  const row = "inline-flex items-center gap-2 text-sm font-semibold transition-colors hover:text-brand";
  return (
    <div className="grid gap-3 md:grid-cols-2 md:gap-4">
      <div className="relative overflow-hidden rounded-2xl bg-surface p-5 shadow-card ring-1 ring-line/70">
        <span aria-hidden className="absolute inset-x-0 top-0 h-1 bg-brand" />
        <div className="flex items-center gap-3">
          <span className="flex size-10 items-center justify-center rounded-xl bg-brand-tint text-brand">
            <Headphones className="size-5" aria-hidden />
          </span>
          <div>
            <h2 className="font-display text-base font-bold leading-tight">Need help with an order?</h2>
            <p className="text-xs text-ink-soft">Customer Care</p>
          </div>
        </div>
        <div className="mt-3 flex flex-col gap-1.5">
          <a href={`tel:${care.tel}`} className={row}>
            <Phone className="size-4 text-brand" aria-hidden /> {care.phone}
          </a>
          <a href={`mailto:${care.email}`} className={`${row} break-all`}>
            <Mail className="size-4 shrink-0 text-brand" aria-hidden /> {care.email}
          </a>
        </div>
      </div>
      <div className="relative isolate flex flex-col justify-between gap-4 overflow-hidden rounded-2xl bg-navy p-5 text-white shadow-card">
        <div aria-hidden className="absolute -right-10 -top-12 -z-10 size-40 rounded-full bg-navy-dark" />
        <div>
          <h2 className="font-display text-lg font-extrabold">Hungry for more?</h2>
          <p className="mt-1 text-sm text-white/80">Browse the Priniti Foods range of snacks, sweets and bakery products.</p>
        </div>
        <ButtonLink href="/shop" variant="light" size="md" className="h-12 w-fit px-6">
          Continue Shopping
        </ButtonLink>
      </div>
    </div>
  );
}
