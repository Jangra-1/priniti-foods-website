import { MessageSquareText } from "lucide-react";
import { ReviewCard } from "@/components/commerce/ReviewCard";
import { Container } from "@/components/layout/Container";
import { ButtonLink } from "@/components/ui/Button";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { homeContent } from "@/data/home";
import type { Review } from "@/types/review";

/** Shows real reviews only. With none, an honest empty state is shown instead of sample content. */
export function ReviewsSection({ reviews }: { reviews: Review[] }) {
  const { heading, emptyTitle, emptyText } = homeContent.reviews;
  return (
    <section aria-labelledby="reviews-heading" className="bg-surface py-8 lg:py-10">
      <Container>
        <SectionHeading id="reviews-heading" eyebrow="Customer love" title={heading} align="center" className="mb-5" />
        {reviews.length === 0 ? (
          <div className="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-line bg-canvas px-6 py-8 text-center">
            <span className="flex size-11 items-center justify-center rounded-full bg-navy-tint text-navy">
              <MessageSquareText className="size-6" aria-hidden />
            </span>
            <div>
              <p className="font-display text-lg font-semibold">{emptyTitle}</p>
              <p className="mt-1 max-w-md text-ink-soft">{emptyText}</p>
            </div>
            <ButtonLink href="/shop" variant="secondary">
              Browse products
            </ButtonLink>
          </div>
        ) : (
          <ul
            role="list"
            className="scrollbar-none -mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:px-6 md:mx-0 md:grid md:grid-cols-3 md:gap-5 md:overflow-visible md:px-0"
          >
            {reviews.map((r) => (
              <li key={r.id} className="w-[85%] shrink-0 snap-start sm:w-[60%] md:w-auto">
                <ReviewCard review={r} />
              </li>
            ))}
          </ul>
        )}
      </Container>
    </section>
  );
}
