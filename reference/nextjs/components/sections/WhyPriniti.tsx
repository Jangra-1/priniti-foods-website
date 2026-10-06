import { Handshake, LayoutGrid, Smile, Sparkles, Wheat, type LucideIcon } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { homeContent } from "@/data/home";

const icons: Record<string, LucideIcon> = { wheat: Wheat, smile: Smile, sparkles: Sparkles, handshake: Handshake, grid: LayoutGrid };

export function WhyPriniti() {
  const { heading, items } = homeContent.why;
  return (
    <section aria-labelledby="why-heading" className="bg-ink py-10 text-white lg:py-12">
      <Container>
        <div className="flex flex-col items-center text-center">
          <Eyebrow className="mb-2 text-lime [&>span]:bg-lime/50">Why choose us</Eyebrow>
          <h2 id="why-heading" className="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
            {heading}
          </h2>
        </div>
        <ul role="list" className="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-5 lg:gap-4">
          {items.map((item) => {
            const Icon = icons[item.icon] ?? Sparkles;
            return (
              <li key={item.title} className="rounded-xl border border-white/10 bg-white/5 p-4 text-center transition-colors hover:bg-white/10">
                <span className="mx-auto flex size-10 items-center justify-center rounded-lg bg-lime/15 text-lime">
                  <Icon className="size-5" aria-hidden />
                </span>
                <h3 className="mt-3 font-display text-sm font-semibold sm:text-base">{item.title}</h3>
                <p className="mt-1 text-xs leading-relaxed text-white/70">{item.text}</p>
              </li>
            );
          })}
        </ul>
      </Container>
    </section>
  );
}
