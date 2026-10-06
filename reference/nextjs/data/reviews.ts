import type { Review } from "@/types/review";

/** SAMPLE ONLY. Not real customers, not real reviews. Replace with the reviews API later. */
export const sampleReviews: Review[] = [
  { id: "sample-1", author: "Sample Customer 1", rating: 5, productName: "Sample Namkeen Mix", isSample: true, body: "Sample review text used to preview how a short review reads in this layout." },
  { id: "sample-2", author: "Sample Customer 2", rating: 4, productName: "Sample Potato Chips", isSample: true, body: "Sample review text used to preview how a slightly longer review wraps across a few lines in the card." },
  { id: "sample-3", author: "Sample Customer 3", rating: 5, productName: "Sample Cookies", isSample: true, body: "Sample review text used to preview a third card in the reviews section." },
];
