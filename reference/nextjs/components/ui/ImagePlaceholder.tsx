import { Image as ImageIcon } from "lucide-react";
import { cn } from "@/lib/cn";

/** Neutral, clearly-labelled stand-in used wherever real photography has not been supplied yet. */
export function ImagePlaceholder({ label = "Image pending", className }: { label?: string; className?: string }) {
  return (
    <div role="img" aria-label={label} className={cn("flex size-full flex-col items-center justify-center gap-1.5 bg-surface p-2 text-center text-ink-soft", className)}>
      <ImageIcon className="size-6" strokeWidth={1.5} aria-hidden />
      <span className="text-xs font-medium leading-tight">{label}</span>
    </div>
  );
}
