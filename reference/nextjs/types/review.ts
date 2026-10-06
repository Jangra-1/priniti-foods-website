export interface Review {
  id: string;
  author: string;
  rating: number;
  body: string;
  productName: string;
  /** Sample rows must never be presented as real customer reviews. */
  isSample: boolean;
}
