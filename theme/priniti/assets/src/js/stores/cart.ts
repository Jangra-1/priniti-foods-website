import { create } from "zustand";
import { createJSONStorage, persist } from "zustand/middleware";
import { config } from "@theme/config";
import { decodeEntities, fromMinor, storeApi, StoreApiError, type StoreApiCart } from "@theme/lib/store-api";
import { toast } from "@theme/stores/toast";
import type { CartLine } from "@theme/types";

/**
 * Cart state backed by the WooCommerce Store API (replaces reference/nextjs/store/cart.ts, which kept the
 * cart in localStorage). WooCommerce is the single source of truth for lines, prices and limits.
 */

export function toLines(cart: StoreApiCart): CartLine[] {
  return cart.items.map((item) => {
    const minor = item.prices.currency_minor_unit;
    const price = fromMinor(item.prices.price, minor);
    const regular = fromMinor(item.prices.regular_price, minor);
    const image = item.images[0];
    const packFromData = item.item_data.find((d) => /pack/i.test(d.name ?? d.key ?? ""));
    const variantLabel = item.variation.length
      ? item.variation.map((v) => decodeEntities(v.value)).join(" · ")
      : packFromData
        ? decodeEntities(packFromData.display ?? packFromData.value)
        : "";
    return {
      key: item.key,
      id: item.id,
      name: decodeEntities(item.name),
      href: item.permalink,
      image: image ? { src: image.thumbnail || image.src, alt: decodeEntities(image.alt || item.name) } : undefined,
      variantLabel,
      variation: item.variation,
      price,
      mrp: Math.max(regular, price),
      quantity: item.quantity,
      maxQuantity: Math.min(item.quantity_limits.maximum || config.commerce.maxQuantityPerLine, config.commerce.maxQuantityPerLine),
    };
  });
}

interface CartState {
  status: "idle" | "loading" | "ready" | "error";
  lines: CartLine[];
  itemCount: number;
  /** Items subtotal as WooCommerce reports it (tax included only when the store displays prices incl. tax). */
  subtotal: number;
  load: () => Promise<void>;
  setQuantity: (key: string, quantity: number) => Promise<void>;
  removeItem: (key: string) => Promise<void>;
  addItem: (input: { id: number; quantity?: number; variation?: { attribute: string; value: string }[]; name?: string }) => Promise<boolean>;
}

function applyCart(cart: StoreApiCart): Pick<CartState, "status" | "lines" | "itemCount" | "subtotal"> {
  const minor = cart.totals.currency_minor_unit;
  const items = fromMinor(cart.totals.total_items, minor);
  const tax = fromMinor(cart.totals.total_items_tax, minor);
  return {
    status: "ready",
    lines: toLines(cart),
    itemCount: cart.items_count,
    subtotal: config.commerce.pricesIncludeTax ? items + tax : items,
  };
}

const fail = (error: unknown) => toast({ title: error instanceof StoreApiError ? error.message : "Could not update your cart", tone: "error" });

export const useCartStore = create<CartState>()((set, get) => ({
  status: "idle",
  lines: [],
  itemCount: 0,
  subtotal: 0,

  load: async () => {
    if (!config.storeApi.enabled) return;
    set({ status: get().status === "ready" ? "ready" : "loading" });
    try {
      set(applyCart(await storeApi.getCart()));
    } catch {
      set({ status: "error" });
    }
  },

  setQuantity: async (key, quantity) => {
    const previous = get().lines;
    const line = previous.find((l) => l.key === key);
    if (!line) return;
    const q = Math.min(Math.max(quantity, 1), line.maxQuantity);
    // Optimistic: the stepper responds immediately, WooCommerce confirms (or the line is restored).
    set({ lines: previous.map((l) => (l.key === key ? { ...l, quantity: q } : l)) });
    try {
      set(applyCart(await storeApi.updateItem(key, q)));
    } catch (error) {
      set({ lines: previous });
      fail(error);
    }
  },

  removeItem: async (key) => {
    const previous = get().lines;
    set({ lines: previous.filter((l) => l.key !== key) });
    try {
      set(applyCart(await storeApi.removeItem(key)));
    } catch (error) {
      set({ lines: previous });
      fail(error);
    }
  },

  addItem: async ({ id, quantity = 1, variation, name }) => {
    try {
      set(applyCart(await storeApi.addItem({ id, quantity, variation })));
      toast({ title: "Added to cart", description: name });
      return true;
    } catch (error) {
      fail(error);
      return false;
    }
  },
}));

/**
 * "Save for later" (reference CartView/CartDrawer). WooCommerce has no saved-items list, so saved lines stay
 * in this browser only, as in the reference. Moving one back re-adds it to the WooCommerce cart.
 */
export type SavedLine = Omit<CartLine, "key" | "maxQuantity">;

interface SavedState {
  saved: SavedLine[];
  save: (line: CartLine) => void;
  remove: (id: number, variantLabel: string) => void;
}

export const useSavedStore = create<SavedState>()(
  persist(
    (set) => ({
      saved: [],
      save: ({ key: _key, maxQuantity: _max, ...line }) =>
        set((s) => ({ saved: [...s.saved.filter((l) => !(l.id === line.id && l.variantLabel === line.variantLabel)), line] })),
      remove: (id, variantLabel) => set((s) => ({ saved: s.saved.filter((l) => !(l.id === id && l.variantLabel === variantLabel)) })),
    }),
    { name: "priniti-saved-v1", version: 1, storage: createJSONStorage(() => localStorage) },
  ),
);

export async function saveForLater(line: CartLine) {
  useSavedStore.getState().save(line);
  await useCartStore.getState().removeItem(line.key);
}
