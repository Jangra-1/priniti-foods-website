import type { ReactNode } from "react";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { Container } from "./Container";

interface PageHeaderProps {
  eyebrow: string;
  title: string;
  description?: string;
  children?: ReactNode;
}

/** Compact page intro in the same style as the homepage hero (soft red tint, no oversized spacing). */
export function PageHeader({ eyebrow, title, description, children }: PageHeaderProps) {
  return (
    <section className="bg-linear-to-br from-brand-tint via-blush to-surface">
      <Container className="py-7 lg:py-9">
        <Eyebrow className="mb-2">{eyebrow}</Eyebrow>
        <h1 className="font-display text-3xl font-extrabold tracking-tight sm:text-4xl">{title}</h1>
        {description ? <p className="mt-2 max-w-2xl text-base leading-relaxed text-ink-soft">{description}</p> : null}
        {children ? <div className="mt-4">{children}</div> : null}
      </Container>
    </section>
  );
}
