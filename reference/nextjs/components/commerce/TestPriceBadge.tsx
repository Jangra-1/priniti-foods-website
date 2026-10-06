/** Marks a price as a development TEST price (not a real Priniti price). */
export function TestPriceBadge({ className = "" }: { className?: string }) {
  return (
    <span
      title="Development test price. Not a real Priniti Foods price."
      className={`inline-flex items-center rounded-full border border-brand/40 bg-brand-tint px-2 py-0.5 text-[10px] font-bold uppercase leading-none tracking-wide text-brand ${className}`}
    >
      Test price
    </span>
  );
}
