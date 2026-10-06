import { ReviewCard } from "@/components/commerce/ReviewCard";
import { Section } from "@/components/layout/Section";
import type { Review } from "@/types/review";

export function ReviewsSection({ reviews }: { reviews: Review[] }) {
  const hasSample = reviews.some((r) => r.isSample);
  return (
    <Section
      id="reviews"
      title="Customer reviews"
      description={hasSample ? "Sample reviews shown for layout only. Real customer reviews will replace them." : undefined}
    >
      <ul
        role="list"
        className="scrollbar-none -mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:px-6 md:mx-0 md:grid md:grid-cols-3 md:gap-5 md:overflow-visible md:px-0"
      >
        {reviews.map((r) => (
          <li key={r.id} className="w-[82%] shrink-0 snap-start sm:w-[60%] md:w-auto">
            <ReviewCard review={r} />
          </li>
        ))}
      </ul>
    </Section>
  );
}
