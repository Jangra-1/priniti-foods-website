import { create } from "zustand";
import { createJSONStorage, persist } from "zustand/middleware";

/** Port of reference/nextjs/store/wishlist.ts: the wishlist stays in this browser. Ids are WooCommerce product ids. */
interface WishlistState {
  ids: string[];
  toggle: (productId: string) => void;
  remove: (productId: string) => void;
}

export const useWishlistStore = create<WishlistState>()(
  persist(
    (set) => ({
      ids: [],
      toggle: (id) => set((s) => ({ ids: s.ids.includes(id) ? s.ids.filter((x) => x !== id) : [...s.ids, id] })),
      remove: (id) => set((s) => ({ ids: s.ids.filter((x) => x !== id) })),
    }),
    { name: "priniti-wishlist-wp-v1", version: 1, storage: createJSONStorage(() => localStorage) },
  ),
);
