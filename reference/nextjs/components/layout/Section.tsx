import type { ReactNode } from "react";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { cn } from "@/lib/cn";
import { Container } from "./Container";

interface SectionProps {
  id: string;
  title: string;
  description?: string;
  href?: string;
  linkLabel?: string;
  tone?: "canvas" | "surface";
  className?: string;
  children: ReactNode;
}

/** Standard homepage/content section: consistent spacing, labelled landmark, optional full-bleed white band. */
export function Section({ id, title, description, href, linkLabel, tone = "canvas", className, children }: SectionProps) {
  return (
    <section id={id} aria-labelledby={`${id}-heading`} className={cn("scroll-mt-24 py-12 lg:py-20", tone === "surface" && "border-y border-line bg-surface", className)}>
      <Container>
        <SectionHeading id={`${id}-heading`} title={title} description={description} href={href} linkLabel={linkLabel} />
        <div className="mt-6 lg:mt-10">{children}</div>
      </Container>
    </section>
  );
}
