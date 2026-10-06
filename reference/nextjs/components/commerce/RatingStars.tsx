import { Star } from "lucide-react";
import { cn } from "@/lib/cn";

interface RatingStarsProps {
  rating: number;
  reviewCount?: number;
  size?: "sm" | "md";
  className?: string;
}

export function RatingStars({ rating, reviewCount, size = "sm", className }: RatingStarsProps) {
  const pct = (Math.max(0, Math.min(5, rating)) / 5) * 100;
  const dim = size === "sm" ? "size-3.5" : "size-5";
  const row = (color: string) =>
    Array.from({ length: 5 }, (_, i) => <Star key={i} aria-hidden className={cn("shrink-0 fill-current", dim, color)} />);

  return (
    <div className={cn("inline-flex items-center gap-1.5", className)}>
      <span role="img" aria-label={`Rated ${rating.toFixed(1)} out of 5`} className="relative inline-flex">
        <span className="flex text-line">{row("")}</span>
        <span className="absolute inset-y-0 left-0 flex overflow-hidden text-navy" style={{ width: `${pct}%` }}>
          {row("")}
        </span>
      </span>
      <span className="text-xs tabular-nums text-ink-soft">
        {rating.toFixed(1)}
        {reviewCount !== undefined ? ` (${reviewCount})` : ""}
      </span>
    </div>
  );
}
