import { TriangleAlert } from "lucide-react";
import { pendingIntegrations } from "@/data/integrations";
import { testPricingEnabled } from "@/data/test-pricing.config";

/** Plain-language list of what is still missing before checkout can go live. */
export function IntegrationStatus() {
  return (
    <section aria-labelledby="not-live-heading" className="rounded-card border border-brand/30 bg-brand-tint p-5 sm:p-6">
      <div className="flex items-start gap-3">
        <TriangleAlert className="mt-0.5 size-6 shrink-0 text-brand" aria-hidden />
        <div>
          <h2 id="not-live-heading" className="font-display text-lg font-bold">
            Checkout is not live
          </h2>
          <p className="mt-1 text-ink-soft">
            This page previews the checkout layout only. No order can be placed, no payment is taken and no details are saved or sent.
          </p>
        </div>
      </div>
      <ul role="list" className="mt-4 grid gap-2 sm:grid-cols-2">
        {pendingIntegrations.map((item) => (
          <li key={item.id} className="rounded-xl bg-surface p-3">
            <p className="flex items-center justify-between gap-2 text-sm font-semibold">
              {item.label}
              <span className="rounded-full bg-ink/8 px-2 py-0.5 text-xs font-semibold text-ink-soft">Pending</span>
            </p>
            <p className="mt-1 text-xs text-ink-soft">
              {item.id === "pricing" && testPricingEnabled ? "Development TEST prices are showing. Real MRP and selling prices have not been supplied." : item.detail}
            </p>
          </li>
        ))}
      </ul>
    </section>
  );
}
