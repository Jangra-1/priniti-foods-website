import { cn } from "@/lib/cn";
import { discountPercent, formatINR } from "@/lib/format";
import { TestPriceBadge } from "./TestPriceBadge";

const sizes = { sm: "text-base", md: "text-xl", lg: "text-3xl" } as const;

interface PriceDisplayProps {
  mrp: number;
  price: number;
  size?: keyof typeof sizes;
  showDiscount?: boolean;
  /** Price is a development TEST price: show the label. */
  isTest?: boolean;
  className?: string;
}

export function PriceDisplay({ mrp, price, size = "md", showDiscount = true, isTest = false, className }: PriceDisplayProps) {
  const off = discountPercent(mrp, price);
  return (
    <div className={cn("flex flex-wrap items-baseline gap-x-2 gap-y-0.5", className)}>
      <span className={cn("font-display font-bold tabular-nums text-ink", sizes[size])}>
        <span className="sr-only">Price </span>
        {formatINR(price)}
      </span>
      {isTest ? <TestPriceBadge /> : null}
      {off > 0 ? (
        <>
          <span className="text-sm tabular-nums text-ink-soft">
            <span className="sr-only">MRP </span>
            <del>{formatINR(mrp)}</del>
          </span>
          {showDiscount ? <span className="text-sm font-semibold text-leaf">{off}% off</span> : null}
        </>
      ) : null}
    </div>
  );
}
