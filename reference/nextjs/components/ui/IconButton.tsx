import type { ButtonHTMLAttributes } from "react";
import { cn } from "@/lib/cn";

export const iconButtonStyles =
  "relative inline-flex size-11 shrink-0 items-center justify-center rounded-full text-ink transition duration-200 hover:bg-ink/5 active:scale-95";

interface IconButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  /** Required: icon-only buttons need an accessible name. */
  label: string;
  badge?: number;
}

export function IconButton({ label, badge, className, children, type = "button", ...props }: IconButtonProps) {
  return (
    <button type={type} aria-label={label} className={cn(iconButtonStyles, className)} {...props}>
      {children}
      {badge ? (
        <span
          aria-hidden
          className="absolute right-0.5 top-0.5 flex min-w-5 items-center justify-center rounded-full bg-brand px-1 text-[11px] font-bold leading-5 text-white"
        >
          {badge > 99 ? "99+" : badge}
        </span>
      ) : null}
    </button>
  );
}
