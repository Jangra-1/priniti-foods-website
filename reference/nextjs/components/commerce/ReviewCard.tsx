import { Badge } from "@/components/ui/Badge";
import type { Review } from "@/types/review";
import { RatingStars } from "./RatingStars";

export function ReviewCard({ review }: { review: Review }) {
  return (
    <article className="flex h-full flex-col gap-4 rounded-card border border-line bg-surface p-6">
      <div className="flex items-center justify-between gap-3">
        <RatingStars rating={review.rating} size="md" />
        {review.isSample ? <Badge>Sample review</Badge> : null}
      </div>
      <p className="flex-1 leading-relaxed text-ink">{review.body}</p>
      <footer className="border-t border-line pt-4 text-sm">
        <p className="font-semibold">{review.author}</p>
        <p className="text-ink-soft">Product: {review.productName}</p>
      </footer>
    </article>
  );
}
