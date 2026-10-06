import { cn } from "@/lib/cn";

/** Small uppercase label flanked by short rules, as used above section titles in the design. */
export function Eyebrow({ children, className }: { children: string; className?: string }) {
  return (
    <p className={cn("flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.18em] text-brand", className)}>
      <span aria-hidden className="h-px w-6 bg-brand/50" />
      {children}
      <span aria-hidden className="h-px w-6 bg-brand/50" />
    </p>
  );
}
