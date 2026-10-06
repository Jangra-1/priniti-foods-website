import Link from "next/link";
import { ArrowRight } from "lucide-react";
import { cn } from "@/lib/cn";
import { Eyebrow } from "./Eyebrow";

interface SectionHeadingProps {
  id?: string;
  title: string;
  eyebrow?: string;
  description?: string;
  href?: string;
  linkLabel?: string;
  align?: "left" | "center";
  className?: string;
}

export function SectionHeading({ id, title, eyebrow, description, href, linkLabel = "View all", align = "left", className }: SectionHeadingProps) {
  const centered = align === "center";
  return (
    <div className={cn("flex items-end justify-between gap-4", centered && "flex-col items-center text-center", className)}>
      <div className={cn("max-w-xl", centered && "flex max-w-2xl flex-col items-center")}>
        {eyebrow ? <Eyebrow className="mb-2">{eyebrow}</Eyebrow> : null}
        <h2 id={id} className="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
          {title}
        </h2>
        {description ? <p className="mt-1.5 text-sm text-ink-soft sm:text-base">{description}</p> : null}
      </div>
      {href ? (
        <Link href={href} className="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-brand transition-colors hover:text-brand-dark">
          {linkLabel}
          <ArrowRight className="size-4" aria-hidden />
        </Link>
      ) : null}
    </div>
  );
}
