"use client";

import { ChevronLeft, ChevronRight } from "lucide-react";
import { Children, useCallback, useEffect, useRef, useState, type ReactNode } from "react";
import { IconButton } from "@/components/ui/IconButton";

interface ProductCarouselProps {
  label: string;
  /** Section heading shown on the same row as the arrows. */
  header?: ReactNode;
  /** Pass server-rendered <ProductCard/> elements as children so cards stay server components. */
  children: ReactNode;
}

export function ProductCarousel({ label, header, children }: ProductCarouselProps) {
  const track = useRef<HTMLUListElement>(null);
  const [canPrev, setCanPrev] = useState(false);
  const [canNext, setCanNext] = useState(false);

  const update = useCallback(() => {
    const el = track.current;
    if (!el) return;
    setCanPrev(el.scrollLeft > 4);
    setCanNext(el.scrollLeft + el.clientWidth < el.scrollWidth - 4);
  }, []);

  useEffect(() => {
    update();
    window.addEventListener("resize", update);
    return () => window.removeEventListener("resize", update);
  }, [update]);

  const scrollBy = (dir: 1 | -1) => {
    const el = track.current;
    if (el) el.scrollBy({ left: dir * el.clientWidth * 0.85, behavior: "smooth" });
  };

  return (
    <section aria-roledescription="carousel" aria-label={label} className="relative">
      <div className="mb-4 flex items-end justify-between gap-4 lg:mb-5">
        <div>{header}</div>
        <div className="hidden shrink-0 gap-2 md:flex">
        <IconButton label="Previous products" onClick={() => scrollBy(-1)} disabled={!canPrev} className="size-10 border border-line bg-surface shadow-card hover:bg-surface disabled:opacity-35">
          <ChevronLeft className="size-5" />
        </IconButton>
        <IconButton label="Next products" onClick={() => scrollBy(1)} disabled={!canNext} className="size-10 bg-brand text-white shadow-card hover:bg-brand-dark disabled:opacity-35">
          <ChevronRight className="size-5" />
        </IconButton>
        </div>
      </div>
      <ul
        ref={track}
        role="list"
        onScroll={update}
        className="scrollbar-none -mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth px-4 pb-2 sm:-mx-6 sm:px-6 lg:mx-0 lg:gap-4 lg:px-0"
      >
        {Children.toArray(children).map((child, i) => (
          <li key={i} className="w-[46%] shrink-0 snap-start sm:w-[32%] md:w-[24%] lg:w-[19%]">
            {child}
          </li>
        ))}
      </ul>
    </section>
  );
}
