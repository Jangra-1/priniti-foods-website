import { testPricingEnabled } from "@/data/test-pricing.config";

/** Shown on every page while test pricing is on, so test prices can never be mistaken for real ones. */
export function TestPriceBanner() {
  if (!testPricingEnabled) return null;
  return (
    <div role="status" className="bg-brand px-4 py-1 text-center text-[11px] font-semibold leading-5 text-white sm:text-xs">
      TEST PRICES ENABLED: prices shown are development placeholders, not real Priniti Foods prices. Checkout is not live.
    </div>
  );
}
