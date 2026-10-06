"use client";

import { Menu, Search, ShoppingBag, User } from "lucide-react";
import Link from "next/link";
import { IconButton, iconButtonStyles } from "@/components/ui/IconButton";
import { desktopNav } from "@/data/navigation";
import { useHydrated } from "@/hooks/useHydrated";
import { useScrolled } from "@/hooks/useScrolled";
import { cn } from "@/lib/cn";
import { selectItemCount, useCartStore } from "@/store/cart";
import { useUIStore } from "@/store/ui";
import type { SearchIndexItem } from "@/types/catalog";
import type { Category } from "@/types/category";
import { Container } from "./Container";
import { DesktopNav } from "./DesktopNav";
import { Logo } from "./Logo";
import { MobileNavigation } from "./MobileNavigation";
import { SearchModal } from "./SearchModal";

export function Header({ categories, searchIndex }: { categories: Category[]; searchIndex: SearchIndexItem[] }) {
  const scrolled = useScrolled();
  const hydrated = useHydrated();
  const cartCount = useCartStore(selectItemCount);
  const count = hydrated ? cartCount : 0;
  const openCart = useUIStore((s) => s.openCart);
  const openSearch = useUIStore((s) => s.openSearch);
  const openMobileNav = useUIStore((s) => s.openMobileNav);

  return (
    <>
      <header
        className={cn(
          "sticky top-0 z-40 border-b transition-[background-color,box-shadow,border-color] duration-200",
          scrolled ? "border-line bg-surface/95 shadow-soft backdrop-blur" : "border-line/70 bg-surface",
        )}
      >
        <Container className={cn("grid grid-cols-[1fr_auto_1fr] items-center gap-2 transition-[height] duration-200 lg:flex lg:justify-between lg:gap-6", "h-14")}>
          <div className="lg:hidden">
            <IconButton label="Open menu" onClick={openMobileNav} className="-ml-2 size-9">
              <Menu className="size-5" />
            </IconButton>
          </div>

          <Link href="/" aria-label="Priniti Foods home" className="justify-self-center rounded-lg lg:justify-self-auto">
            <Logo />
          </Link>

          <DesktopNav items={desktopNav} categories={categories} />

          <div className="flex items-center justify-end gap-0.5">
            <button
              type="button"
              onClick={openSearch}
              aria-label="Search products"
              className="mr-2 hidden h-9 w-48 items-center gap-2 rounded-full bg-navy-tint/70 px-3.5 text-left text-[13px] text-ink-soft transition-colors hover:bg-navy-tint lg:flex xl:w-60"
            >
              <Search className="size-4 shrink-0" aria-hidden />
              Search snacks...
            </button>
            <IconButton label="Search" onClick={openSearch} className="size-9 lg:hidden">
              <Search className="size-[18px]" />
            </IconButton>
            <Link href="/login" aria-label="Account" className={cn(iconButtonStyles, "hidden size-9 sm:inline-flex")}>
              <User className="size-[18px]" />
            </Link>
            <IconButton label={count ? `Cart, ${count} ${count === 1 ? "item" : "items"}` : "Cart"} badge={count} onClick={openCart} className="-mr-2 size-9 lg:mr-0">
              <ShoppingBag className="size-[18px]" />
            </IconButton>
          </div>
        </Container>
      </header>
      <MobileNavigation categories={categories} />
      <SearchModal categories={categories} index={searchIndex} />
    </>
  );
}
