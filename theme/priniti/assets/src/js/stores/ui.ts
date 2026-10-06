import { create } from "zustand";

/** Port of reference/nextjs/store/ui.ts (quick view arrives with the product-card phase). */
interface UIState {
  cartOpen: boolean;
  mobileNavOpen: boolean;
  searchOpen: boolean;
  openCart: () => void;
  closeCart: () => void;
  openMobileNav: () => void;
  closeMobileNav: () => void;
  openSearch: () => void;
  closeSearch: () => void;
}

export const useUIStore = create<UIState>()((set) => ({
  cartOpen: false,
  mobileNavOpen: false,
  searchOpen: false,
  openCart: () => set({ cartOpen: true }),
  closeCart: () => set({ cartOpen: false }),
  openMobileNav: () => set({ mobileNavOpen: true }),
  closeMobileNav: () => set({ mobileNavOpen: false }),
  openSearch: () => set({ searchOpen: true }),
  closeSearch: () => set({ searchOpen: false }),
}));
