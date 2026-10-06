import type { AnchorHTMLAttributes, ButtonHTMLAttributes } from "react";
import { cn } from "@theme/lib/cn";

/** Port of reference/nextjs/components/ui/Button.tsx (ButtonLink renders a plain <a> instead of next/link). */
export type ButtonVariant = "primary" | "secondary" | "outline" | "light" | "ghost" | "dark";
export type ButtonSize = "sm" | "md" | "lg";

interface StyleOptions {
  variant?: ButtonVariant;
  size?: ButtonSize;
  fullWidth?: boolean;
}

const variants: Record<ButtonVariant, string> = {
  primary: "bg-brand text-white hover:bg-brand-dark hover:shadow-[0_10px_24px_-10px_rgb(227_0_22/0.7)]",
  secondary: "border border-ink/20 bg-surface text-ink hover:border-ink hover:bg-canvas",
  outline: "border-2 border-ink bg-transparent text-ink hover:bg-ink hover:text-white",
  light: "bg-white text-brand hover:bg-white/90",
  ghost: "text-ink hover:bg-ink/5",
  dark: "bg-ink text-white hover:bg-ink/85",
};

const sizes: Record<ButtonSize, string> = {
  sm: "h-9 px-4 text-sm",
  md: "h-11 px-5 text-sm",
  lg: "h-14 px-8 text-base",
};

export function buttonStyles({ variant = "primary", size = "md", fullWidth }: StyleOptions = {}) {
  return cn(
    "inline-flex select-none items-center justify-center gap-2 rounded-xl font-semibold shadow-none transition duration-200 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50",
    variants[variant],
    sizes[size],
    fullWidth && "w-full",
  );
}

type ButtonProps = StyleOptions & ButtonHTMLAttributes<HTMLButtonElement>;

export function Button({ variant, size, fullWidth, className, type = "button", ...props }: ButtonProps) {
  return <button type={type} className={cn(buttonStyles({ variant, size, fullWidth }), className)} {...props} />;
}

type ButtonLinkProps = StyleOptions & AnchorHTMLAttributes<HTMLAnchorElement> & { href: string };

export function ButtonLink({ variant, size, fullWidth, className, href, ...props }: ButtonLinkProps) {
  return <a href={href} className={cn(buttonStyles({ variant, size, fullWidth }), className)} {...props} />;
}
