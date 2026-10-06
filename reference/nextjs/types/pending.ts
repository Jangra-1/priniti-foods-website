export type PendingStatus = "review-required" | "image-only" | "website-only";

/** A product that is known but NOT published. Kept here so nothing is silently dropped. */
export interface PendingProduct {
  name: string;
  suggestedSlug: string;
  suggestedCategory?: string; // category slug; undefined = unknown
  status: PendingStatus;
  reason: string;
  /** File in assets/pending-images (not served publicly). */
  imageFile?: string;
  question?: string;
}
