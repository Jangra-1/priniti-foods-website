
import { Minus, Plus } from "lucide-react";
import { config } from "@theme/config";
import { cn } from "@theme/lib/cn";

interface QuantitySelectorProps {
  value: number;
  onChange: (value: number) => void;
  min?: number;
  max?: number;
  size?: "sm" | "md";
  label?: string;
  className?: string;
}

export function QuantitySelector({
  value,
  onChange,
  min = 1,
  max = config.commerce.maxQuantityPerLine,
  size = "md",
  label = "Quantity",
  className,
}: QuantitySelectorProps) {
  const btn = cn(
    "flex items-center justify-center rounded-full text-ink transition hover:bg-ink/5 disabled:opacity-35 disabled:hover:bg-transparent",
    size === "sm" ? "size-8" : "size-10",
  );
  return (
    <div role="group" aria-label={label} className={cn("inline-flex items-center rounded-full border border-line bg-surface p-0.5", className)}>
      <button type="button" aria-label="Decrease quantity" disabled={value <= min} onClick={() => onChange(value - 1)} className={btn}>
        <Minus className="size-4" aria-hidden />
      </button>
      <output aria-live="polite" className="min-w-8 text-center text-sm font-semibold tabular-nums">
        {value}
      </output>
      <button type="button" aria-label="Increase quantity" disabled={value >= max} onClick={() => onChange(value + 1)} className={btn}>
        <Plus className="size-4" aria-hidden />
      </button>
    </div>
  );
}
