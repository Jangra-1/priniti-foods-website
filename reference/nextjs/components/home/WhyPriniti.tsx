import { Heart, LayoutGrid, Smile, Sparkles, Wheat, type LucideIcon } from "lucide-react";
import { Section } from "@/components/layout/Section";
import { home, type WhyIconKey } from "@/data/home";

const icons: Record<WhyIconKey, LucideIcon> = { quality: Wheat, taste: Smile, hygiene: Sparkles, trust: Heart, range: LayoutGrid };

export function WhyPriniti() {
  return (
    <Section id="why-priniti" title="Why Priniti Foods" tone="surface">
      <ul role="list" className="grid gap-x-8 gap-y-7 sm:grid-cols-2 lg:grid-cols-5">
        {home.why.map((item) => {
          const Icon = icons[item.icon];
          return (
            <li key={item.title} className="flex items-start gap-4 lg:flex-col">
              <span className="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-tint text-brand">
                <Icon className="size-6" aria-hidden />
              </span>
              <div>
                <h3 className="font-display text-base font-semibold">{item.title}</h3>
                <p className="mt-1 text-sm leading-relaxed text-ink-soft">{item.text}</p>
              </div>
            </li>
          );
        })}
      </ul>
    </Section>
  );
}
