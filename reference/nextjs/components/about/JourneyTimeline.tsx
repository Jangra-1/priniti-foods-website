import { company } from "@/data/company";

/**
 * Responsive journey timeline: vertical on phones, horizontal from lg up.
 * Every entry comes from data/company.ts (official milestones only).
 */
export function JourneyTimeline() {
  const items = company.milestones;
  return (
    <ol className="relative grid gap-5 lg:grid-cols-7 lg:gap-3">
      <div
        aria-hidden
        className="absolute bottom-6 left-7 top-6 border-l-2 border-dashed border-brand/40 lg:bottom-auto lg:left-8 lg:right-8 lg:top-7 lg:border-l-0 lg:border-t-2"
      />
      {items.map((m, i) => (
        <li key={`${m.year}-${m.title}`} className="relative flex gap-4 lg:flex-col lg:items-center lg:gap-3 lg:text-center">
          <span
            className={`relative z-10 flex size-14 shrink-0 items-center justify-center rounded-full font-display text-sm font-extrabold text-white shadow-lift ring-4 ring-surface ${
              i === items.length - 1 ? "bg-navy" : "bg-brand"
            }`}
          >
            {m.year}
          </span>
          <div className="pt-1 lg:pt-0">
            <h3 className="font-display text-sm font-bold leading-snug sm:text-[15px]">{m.title}</h3>
            <p className="mt-1 text-xs leading-relaxed text-ink-soft">{m.text}</p>
          </div>
        </li>
      ))}
    </ol>
  );
}
