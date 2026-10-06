"use client";

import { ChevronRight, User } from "lucide-react";
import Link from "next/link";
import { ButtonLink } from "@/components/ui/Button";
import { Drawer } from "@/components/ui/Drawer";
import { mobileNav } from "@/data/navigation";
import { useUIStore } from "@/store/ui";
import type { Category } from "@/types/category";

const rowStyles = "flex items-center justify-between rounded-xl px-3 py-3.5 text-base font-semibold transition-colors active:bg-brand-tint";

export function MobileNavigation({ categories }: { categories: Category[] }) {
  const open = useUIStore((s) => s.mobileNavOpen);
  const close = useUIStore((s) => s.closeMobileNav);

  return (
    <Drawer open={open} onClose={close} title="Menu" side="left">
      <nav aria-label="Mobile" className="flex flex-col gap-6 p-4">
        <ul role="list">
          {mobileNav.shop.map((l) => (
            <li key={l.href}>
              <Link href={l.href} onClick={close} className={rowStyles}>
                {l.label}
                <ChevronRight className="size-5 text-ink-soft" aria-hidden />
              </Link>
            </li>
          ))}
        </ul>

        <div>
          <h3 className="mb-2 px-3 text-sm font-semibold text-ink-soft">Shop by category</h3>
          <ul role="list" className="grid grid-cols-2 gap-2">
            {categories.map((c) => (
              <li key={c.slug}>
                <Link
                  href={`/category/${c.slug}`}
                  onClick={close}
                  className="flex h-full items-center rounded-2xl border border-line bg-canvas px-3.5 py-3 text-sm font-semibold transition-colors active:border-brand active:bg-brand-tint"
                >
                  {c.name}
                </Link>
              </li>
            ))}
          </ul>
        </div>

        <ul role="list" className="border-t border-line pt-4">
          {mobileNav.info.map((l) => (
            <li key={l.href}>
              <Link href={l.href} onClick={close} className="block rounded-xl px-3 py-2.5 text-sm font-medium text-ink-soft active:bg-brand-tint">
                {l.label}
              </Link>
            </li>
          ))}
        </ul>

        <div className="grid grid-cols-2 gap-2 border-t border-line pt-4">
          <ButtonLink href="/login" variant="secondary" onClick={close}>
            <User className="size-4" aria-hidden /> Log in
          </ButtonLink>
          <ButtonLink href="/signup" onClick={close}>
            Sign up
          </ButtonLink>
        </div>
      </nav>
    </Drawer>
  );
}
