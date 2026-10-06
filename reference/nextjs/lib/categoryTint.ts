const tints = [
  { bg: "bg-brand-tint", text: "text-brand" },
  { bg: "bg-navy-tint", text: "text-navy" },
  { bg: "bg-leaf-tint", text: "text-leaf" },
] as const;

/** Stable colour per category, derived from the slug so it survives reordering. */
export function categoryTint(slug: string) {
  const sum = [...slug].reduce((n, ch) => n + ch.charCodeAt(0), 0);
  return tints[sum % tints.length];
}
