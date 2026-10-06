import type { HTMLAttributes } from "react";
import { cn } from "@/lib/cn";

export type BadgeTone = "brand" | "navy" | "leaf" | "neutral" | "dark";

const tones: Record<BadgeTone, string> = {
  brand: "bg-brand text-white",
  navy: "bg-navy text-white",
  leaf: "bg-leaf-tint text-leaf",
  neutral: "bg-ink/8 text-ink-soft",
  dark: "bg-ink text-white",
};

export function Badge({ tone = "neutral", className, ...props }: HTMLAttributes<HTMLSpanElement> & { tone?: BadgeTone }) {
  return (
    <span
      className={cn("inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold leading-none", tones[tone], className)}
      {...props}
    />
  );
}
