"use client";

import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { useCallback, useMemo, useTransition } from "react";
import { parseFilters, toQueryString } from "@/lib/catalog";
import type { CatalogFilters } from "@/types/catalog";

/** URL is the single source of truth for filters. Any change resets to page 1. */
export function useCatalogParams() {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [isPending, startTransition] = useTransition();

  const filters = useMemo(() => parseFilters(Object.fromEntries(searchParams.entries())), [searchParams]);

  const apply = useCallback(
    (patch: Partial<CatalogFilters>) => {
      const next: CatalogFilters = { ...filters, ...patch, page: patch.page ?? 1 };
      startTransition(() => router.replace(`${pathname}${toQueryString(next)}`, { scroll: false }));
    },
    [filters, pathname, router],
  );

  return { filters, apply, isPending };
}

export const clearedFilters: Partial<CatalogFilters> = { categories: [], price: undefined, rating: undefined, inStock: false };
