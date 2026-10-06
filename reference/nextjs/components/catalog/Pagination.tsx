import { ChevronLeft, ChevronRight } from "lucide-react";
import Link from "next/link";
import { cn } from "@/lib/cn";
import { toQueryString } from "@/lib/catalog";
import type { CatalogFilters } from "@/types/catalog";

interface PaginationProps {
  basePath: string;
  filters: CatalogFilters;
  page: number;
  pageCount: number;
}

export function Pagination({ basePath, filters, page, pageCount }: PaginationProps) {
  if (pageCount <= 1) return null;
  const href = (n: number) => `${basePath}${toQueryString({ ...filters, page: n })}`;
  const item = "flex size-11 items-center justify-center rounded-full border text-sm font-semibold transition-colors";

  return (
    <nav aria-label="Pagination" className="mt-10 flex items-center justify-center gap-2">
      {page > 1 ? (
        <Link href={href(page - 1)} aria-label="Previous page" className={cn(item, "border-line bg-surface hover:border-ink")}>
          <ChevronLeft className="size-5" aria-hidden />
        </Link>
      ) : null}
      {Array.from({ length: pageCount }, (_, i) => i + 1).map((n) => (
        <Link
          key={n}
          href={href(n)}
          aria-label={`Page ${n}`}
          aria-current={n === page ? "page" : undefined}
          className={cn(item, n === page ? "border-ink bg-ink text-white" : "border-line bg-surface hover:border-ink")}
        >
          {n}
        </Link>
      ))}
      {page < pageCount ? (
        <Link href={href(page + 1)} aria-label="Next page" className={cn(item, "border-line bg-surface hover:border-ink")}>
          <ChevronRight className="size-5" aria-hidden />
        </Link>
      ) : null}
    </nav>
  );
}
