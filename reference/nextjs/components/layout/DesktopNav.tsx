"use client";

import { ChevronDown } from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useRef, useState } from "react";
import { cn } from "@/lib/cn";
import type { NavItem } from "@/data/navigation";
import type { Category } from "@/types/category";

const linkStyles = "rounded-full px-3 py-1.5 text-[13px] font-semibold text-ink/80 transition-colors hover:bg-brand-tint hover:text-brand";

function CategoriesMenu({ item, categories }: { item: NavItem; categories: Category[] }) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  return (
    <div
      ref={ref}
      className="relative"
      onMouseEnter={() => setOpen(true)}
      onMouseLeave={() => setOpen(false)}
      onBlur={(e) => {
        if (!ref.current?.contains(e.relatedTarget as Node)) setOpen(false);
      }}
      onKeyDown={(e) => {
        if (e.key === "Escape") setOpen(false);
      }}
    >
      <button
        type="button"
        aria-expanded={open}
        aria-controls="categories-menu"
        onClick={() => setOpen((o) => !o)}
        className={cn(linkStyles, "inline-flex items-center gap-1")}
      >
        {item.label}
        <ChevronDown className={cn("size-4 transition-transform duration-200", open && "rotate-180")} aria-hidden />
      </button>
      {open ? (
        <div id="categories-menu" className="absolute left-1/2 top-full z-50 -translate-x-1/2 pt-3">
          <ul role="list" className="grid w-[26rem] animate-pop grid-cols-2 gap-1 rounded-card border border-line bg-surface p-3 shadow-lift">
            {categories.map((c) => (
              <li key={c.slug}>
                <Link
                  href={`/category/${c.slug}`}
                  onClick={() => setOpen(false)}
                  className="block rounded-xl px-3 py-2.5 transition-colors hover:bg-brand-tint"
                >
                  <span className="block text-sm font-semibold">{c.name}</span>
                  {c.description ? <span className="block truncate text-xs text-ink-soft">{c.description}</span> : null}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      ) : null}
    </div>
  );
}

export function DesktopNav({ items, categories }: { items: NavItem[]; categories: Category[] }) {
  const pathname = usePathname();
  return (
    <nav aria-label="Primary" className="hidden lg:block">
      <ul role="list" className="flex items-center gap-0.5">
        {items.map((item) => (
          <li key={item.label}>
            {item.hasCategoryMenu ? (
              <CategoriesMenu item={item} categories={categories} />
            ) : (
              <Link
                href={item.href}
                aria-current={pathname === item.href.split("?")[0] && !item.href.includes("?") ? "page" : undefined}
                className={cn(linkStyles, pathname === item.href && "bg-brand-tint text-brand")}
              >
                {item.label}
              </Link>
            )}
          </li>
        ))}
      </ul>
    </nav>
  );
}
