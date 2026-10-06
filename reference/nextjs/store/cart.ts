import { create } from "zustand";
import { createJSONStorage, persist } from "zustand/middleware";
import { siteConfig } from "@/data/site";
import { testPricingEnabled } from "@/data/test-pricing.config";
import type { CartLine } from "@/types/cart";

export const lineKey = (l: Pick<CartLine, "productId" | "variantId">) => `${l.productId}:${l.variantId}`;
const clamp = (q: number) => Math.min(Math.max(q, 1), siteConfig.commerce.maxQuantityPerLine);

interface CartState {
  lines: CartLine[];
  saved: CartLine[];
  addItem: (line: Omit<CartLine, "quantity">, quantity?: number) => void;
  setQuantity: (key: string, quantity: number) => void;
  removeItem: (key: string) => void;
  saveForLater: (key: string) => void;
  moveToCart: (key: string) => void;
  removeSaved: (key: string) => void;
  clear: () => void;
}

/** Demo persistence in localStorage only. Replaced by a server cart when the backend exists. */
export const useCartStore = create<CartState>()(
  persist(
    (set) => ({
      lines: [],
      saved: [],
      addItem: (line, quantity = 1) =>
        set((s) => {
          // Safety net: only real, priced items can ever enter the cart.
          if (!Number.isFinite(line.price) || line.price <= 0) return s;
          const key = lineKey(line);
          if (s.lines.some((l) => lineKey(l) === key)) {
            return { lines: s.lines.map((l) => (lineKey(l) === key ? { ...l, quantity: clamp(l.quantity + quantity) } : l)) };
          }
          return { lines: [...s.lines, { ...line, quantity: clamp(quantity) }] };
        }),
      setQuantity: (key, quantity) =>
        set((s) => ({ lines: s.lines.map((l) => (lineKey(l) === key ? { ...l, quantity: clamp(quantity) } : l)) })),
      removeItem: (key) => set((s) => ({ lines: s.lines.filter((l) => lineKey(l) !== key) })),
      saveForLater: (key) =>
        set((s) => {
          const line = s.lines.find((l) => lineKey(l) === key);
          if (!line) return s;
          return {
            lines: s.lines.filter((l) => lineKey(l) !== key),
            saved: [...s.saved.filter((l) => lineKey(l) !== key), line],
          };
        }),
      moveToCart: (key) =>
        set((s) => {
          const line = s.saved.find((l) => lineKey(l) === key);
          if (!line) return s;
          const inCart = s.lines.some((l) => lineKey(l) === key);
          return {
            saved: s.saved.filter((l) => lineKey(l) !== key),
            lines: inCart
              ? s.lines.map((l) => (lineKey(l) === key ? { ...l, quantity: clamp(l.quantity + line.quantity) } : l))
              : [...s.lines, line],
          };
        }),
      removeSaved: (key) => set((s) => ({ saved: s.saved.filter((l) => lineKey(l) !== key) })),
      clear: () => set({ lines: [] }),
    }),
    {
      name: "priniti-cart-v2",
      version: 2,
      storage: createJSONStorage(() => localStorage),
      partialize: (s) => ({ lines: s.lines, saved: s.saved }),
      // When test pricing is switched off, lines that were added with TEST prices are discarded on load.
      merge: (persisted, current) => {
        const p = persisted as Partial<Pick<CartState, "lines" | "saved">> | undefined;
        const keep = (list: CartLine[] = []) => (testPricingEnabled ? list : list.filter((l) => !l.isTestPrice));
        return { ...current, lines: keep(p?.lines), saved: keep(p?.saved) };
      },
    },
  ),
);

export const selectItemCount = (s: CartState) => s.lines.reduce((n, l) => n + l.quantity, 0);
