import { ChevronRight, User } from "lucide-react";
import { config } from "@theme/config";
import { useUIStore } from "@theme/stores/ui";
import { ButtonLink } from "./Button";
import { Drawer } from "./Drawer";

/** Port of reference/nextjs/components/layout/MobileNavigation.tsx (links and categories come from WordPress). */
const rowStyles = "flex items-center justify-between rounded-xl px-3 py-3.5 text-base font-semibold transition-colors active:bg-brand-tint";

export function MobileNavigation() {
  const open = useUIStore((s) => s.mobileNavOpen);
  const close = useUIStore((s) => s.closeMobileNav);
  const { nav, categories, urls } = config;

  return (
    <Drawer open={open} onClose={close} title="Menu" side="left">
      <nav aria-label="Mobile" className="flex flex-col gap-6 p-4">
        <ul role="list">
          {nav.mobileShop.map((l) => (
            <li key={l.href}>
              <a href={l.href} onClick={close} className={rowStyles}>
                {l.label}
                <ChevronRight className="size-5 text-ink-soft" aria-hidden />
              </a>
            </li>
          ))}
        </ul>

        <div>
          <h3 className="mb-2 px-3 text-sm font-semibold text-ink-soft">Shop by category</h3>
          <ul role="list" className="grid grid-cols-2 gap-2">
            {categories.map((c) => (
              <li key={c.slug}>
                <a
                  href={c.href}
                  onClick={close}
                  className="flex h-full items-center rounded-2xl border border-line bg-canvas px-3.5 py-3 text-sm font-semibold transition-colors active:border-brand active:bg-brand-tint"
                >
                  {c.name}
                </a>
              </li>
            ))}
          </ul>
        </div>

        <ul role="list" className="border-t border-line pt-4">
          {nav.mobileInfo.map((l) => (
            <li key={l.href}>
              <a href={l.href} onClick={close} className="block rounded-xl px-3 py-2.5 text-sm font-medium text-ink-soft active:bg-brand-tint">
                {l.label}
              </a>
            </li>
          ))}
        </ul>

        <div className="grid grid-cols-2 gap-2 border-t border-line pt-4">
          <ButtonLink href={urls.login} variant="secondary" onClick={close}>
            <User className="size-4" aria-hidden /> Log in
          </ButtonLink>
          <ButtonLink href={urls.signup} onClick={close}>
            Sign up
          </ButtonLink>
        </div>
      </nav>
    </Drawer>
  );
}
