import { Container } from "@/components/layout/Container";

export interface Stat {
  value: string;
  label: string;
}

/** Facts come from the live catalog (product and category counts) and the pack tagline. Nothing invented. */
export function StatsBand({ title, description, stats }: { title: string; description: string; stats: Stat[] }) {
  return (
    <section aria-labelledby="stats-heading" className="relative isolate overflow-hidden bg-brand py-9 text-white lg:py-10">
      <div aria-hidden className="absolute -right-20 -top-24 -z-10 size-80 rounded-full bg-navy" />
      <div aria-hidden className="absolute -bottom-28 left-10 -z-10 size-64 rounded-full bg-brand-dark/60" />
      <Container>
        <div className="text-center">
          <h2 id="stats-heading" className="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
            {title}
          </h2>
          <p className="mt-1 text-sm text-white/85">{description}</p>
        </div>
        <dl className="mt-6 grid grid-cols-3 gap-4">
          {stats.map((s) => (
            <div key={s.label} className="text-center">
              <dt className="order-2 mt-1 text-[10px] font-bold uppercase tracking-[0.16em] text-white/80 sm:text-xs">{s.label}</dt>
              <dd className="font-display text-3xl font-extrabold tracking-tight sm:text-5xl">{s.value}</dd>
              <span aria-hidden className="mx-auto mt-2 block h-1 w-8 rounded-full bg-white/50" />
            </div>
          ))}
        </dl>
      </Container>
    </section>
  );
}
