import { sampleReviews } from "@/data/reviews";
import type { Review } from "@/types/review";

// Swap for a fetch() to the reviews backend later. Sample rows are never returned to the storefront:
// the catalog is real now, so sample reviews of non-existent sample products would mislead.
export async function getFeaturedReviews(limit = 3): Promise<Review[]> {
  return sampleReviews.filter((r) => !r.isSample).slice(0, limit);
}
