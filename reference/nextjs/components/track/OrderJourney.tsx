import { BadgeCheck, Package, PackageCheck, ShoppingBag, Truck } from "lucide-react";

const stages = [
  { label: "Order Placed", icon: ShoppingBag },
  { label: "Confirmed", icon: BadgeCheck },
  { label: "Packed", icon: Package },
  { label: "Shipped", icon: Truck },
  { label: "Delivered", icon: PackageCheck },
] as const;

/**
 * ILLUSTRATIVE order journey. No stage is ever marked complete or active: there is no order to show.
 * Vertical on phones, horizontal from md up.
 */
export function OrderJourney() {
  return (
    <div>
      <ol className="relative grid gap-4 md:grid-cols-5 md:gap-2">
        <div
          aria-hidden
          className="absolute bottom-6 left-6 top-6 border-l-2 border-dashed border-line md:bottom-auto md:left-10 md:right-10 md:top-6 md:border-l-0 md:border-t-2"
        />
        {stages.map((s, i) => {
          const Icon = s.icon;
          return (
            <li key={s.label} className="relative flex items-center gap-4 md:flex-col md:gap-2.5 md:text-center">
              <span className="relative z-10 flex size-12 shrink-0 items-center justify-center rounded-full bg-canvas text-ink-soft ring-4 ring-surface">
                <span className="absolute inset-0 rounded-full border-2 border-dashed border-line" aria-hidden />
                <Icon className="size-5" aria-hidden />
              </span>
              <div>
                <p className="text-[11px] font-bold uppercase tracking-wide text-ink-soft">Step {i + 1}</p>
                <p className="font-display text-sm font-bold leading-tight">{s.label}</p>
              </div>
            </li>
          );
        })}
      </ol>
      <p className="mt-4 text-center text-xs text-ink-soft">These stages are illustrative only. No order is being shown.</p>
    </div>
  );
}
