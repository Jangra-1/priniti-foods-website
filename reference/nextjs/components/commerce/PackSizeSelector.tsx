"use client";

import { useId } from "react";
import { cn } from "@/lib/cn";
import type { PackVariant } from "@/types/product";

interface PackSizeSelectorProps {
  variants: PackVariant[];
  value: string | undefined;
  onChange: (variantId: string) => void;
}

/** Only verified pack sizes are ever shown. A single size is shown as a selected option too. */
export function PackSizeSelector({ variants, value, onChange }: PackSizeSelectorProps) {
  const name = useId();
  return (
    <fieldset>
      <legend className="mb-2 text-sm font-semibold">Pack size</legend>
      <div className="flex flex-wrap gap-2">
        {variants.map((v) => (
          <label key={v.id} className="relative cursor-pointer">
            <input
              type="radio"
              name={name}
              value={v.id}
              checked={value === v.id}
              onChange={() => onChange(v.id)}
              className="peer sr-only"
            />
            <span
              className={cn(
                "flex min-h-11 min-w-20 items-center justify-center rounded-full border px-5 text-sm font-semibold transition-colors",
                "border-line bg-surface hover:border-ink peer-checked:border-navy peer-checked:bg-navy peer-checked:text-white",
                "peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand",
              )}
            >
              {v.label}
            </span>
          </label>
        ))}
      </div>
      {variants.some((v) => v.id === value && v.source === "image-filename") ? (
        <p className="mt-2 text-xs text-ink-soft">Pack size to be confirmed.</p>
      ) : null}
      {variants.some((v) => v.id === value && v.source === "test-placeholder") ? (
        <p className="mt-2 text-xs text-ink-soft">Placeholder pack used for testing. The real pack size is to be confirmed.</p>
      ) : null}
    </fieldset>
  );
}
