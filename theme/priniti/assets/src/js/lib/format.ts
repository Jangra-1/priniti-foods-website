const whole = new Intl.NumberFormat("en-IN", { style: "currency", currency: "INR", maximumFractionDigits: 0 });
const paise = new Intl.NumberFormat("en-IN", { style: "currency", currency: "INR", minimumFractionDigits: 2, maximumFractionDigits: 2 });

/** ₹30, ₹1,250, ₹96.80: paise only when the amount has them (multi-pack prices). Mirrors priniti_format_inr(). */
export const formatINR = (amount: number) => (Math.round(amount * 100) % 100 === 0 ? whole : paise).format(amount);

export function discountPercent(mrp: number, price: number) {
  if (mrp <= 0 || price >= mrp) return 0;
  return Math.round(((mrp - price) / mrp) * 100);
}
