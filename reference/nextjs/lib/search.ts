/** Lower-case, strip punctuation: "Cream 'n' Onion" and "cream n onion" match. */
export const normalize = (s: string) => s.toLowerCase().replace(/[^a-z0-9\s]/g, " ").replace(/\s+/g, " ").trim();

/** Every query word must appear somewhere in the haystack. */
export function matchesQuery(haystack: string, query: string): boolean {
  const hay = normalize(haystack);
  const tokens = normalize(query).split(" ").filter(Boolean);
  return tokens.length > 0 && tokens.every((t) => hay.includes(t));
}
