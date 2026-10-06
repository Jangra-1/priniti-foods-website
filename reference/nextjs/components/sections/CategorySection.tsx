import { CategoryCard } from "@/components/catalog/CategoryCard";
import { Container } from "@/components/layout/Container";
import { SectionHeading } from "@/components/ui/SectionHeading";
import type { Category } from "@/types/category";

export function CategorySection({ categories, counts }: { categories: Category[]; counts: Record<string, number> }) {
  return (
    <section id="categories" aria-labelledby="categories-heading" className="scroll-mt-24 bg-surface py-8 lg:py-10">
      <Container>
        <SectionHeading id="categories-heading" eyebrow="Browse by type" title="Shop Categories" align="center" className="mb-5 lg:mb-6" />
        {/* Phones: swipeable row. md and up: a grid. */}
        <ul
          role="list"
          className="scrollbar-none -mx-4 flex snap-x snap-mandatory gap-2.5 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:px-6 md:mx-0 md:grid md:grid-cols-5 md:gap-3 md:overflow-visible md:px-0 md:pb-0 lg:grid-cols-10 lg:gap-2.5"
        >
          {categories.map((c, i) => (
            <li key={c.slug} className="w-[34%] shrink-0 snap-start sm:w-[24%] md:w-auto">
              <CategoryCard category={c} index={i} productCount={counts[c.slug]} />
            </li>
          ))}
        </ul>
      </Container>
    </section>
  );
}
