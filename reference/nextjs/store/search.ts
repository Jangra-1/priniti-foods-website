import { create } from "zustand";
import { createJSONStorage, persist } from "zustand/middleware";

interface RecentSearchState {
  items: string[];
  add: (q: string) => void;
  clear: () => void;
}

/** Recent searches stay in this browser only. */
export const useRecentSearches = create<RecentSearchState>()(
  persist(
    (set) => ({
      items: [],
      add: (q) =>
        set((s) => ({ items: [q, ...s.items.filter((x) => x.toLowerCase() !== q.toLowerCase())].slice(0, 5) })),
      clear: () => set({ items: [] }),
    }),
    { name: "priniti-recent-searches-v1", version: 1, storage: createJSONStorage(() => localStorage) },
  ),
);
