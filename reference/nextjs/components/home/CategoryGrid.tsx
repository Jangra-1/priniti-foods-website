import { CategoryCard } from "@/components/catalog/CategoryCard";
import { Section } from "@/components/layout/Section";
import type { Category } from "@/types/category";

export function CategoryGrid({ categories, counts }: { categories: Category[]; counts: Record<string, number> }) {
  return (
    <Section id="categories" title="Shop by category" description="Find your favourite kind of snack." href="/shop" linkLabel="Shop all">
      <ul role="list" className="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-4">
        {categories.map((c, i) => (
          <li key={c.slug}>
            <CategoryCard category={c} index={i} productCount={counts[c.slug] ?? 0} />
          </li>
        ))}
      </ul>
    </Section>
  );
}
