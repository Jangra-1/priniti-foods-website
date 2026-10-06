import { createRoot } from "react-dom/client";
import { initCatalog } from "@theme/catalog";
import { CartDrawer } from "@theme/components/CartDrawer";
import { CartView } from "@theme/components/CartView";
import { MobileNavigation } from "@theme/components/MobileNavigation";
import { QuickViewModal } from "@theme/components/QuickViewModal";
import { SearchModal } from "@theme/components/SearchModal";
import { Toaster } from "@theme/components/Toaster";
import { initForms } from "@theme/forms";
import { initHeader } from "@theme/header";
import { initInteractions } from "@theme/interactions";
import { useCartStore } from "@theme/stores/cart";
import { toast } from "@theme/stores/toast";
import { useUIStore } from "@theme/stores/ui";

/**
 * Islands entry. PHP renders every page; this bundle mounts the global overlays that
 * reference/nextjs/app/layout.tsx renders on every page (CartDrawer, QuickViewModal, MobileNavigation,
 * SearchModal, Toaster), the cart page island, and the behaviour of server-rendered components.
 */
function Overlays() {
  return (
    <>
      <MobileNavigation />
      <SearchModal />
      <CartDrawer />
      <QuickViewModal />
      <Toaster />
    </>
  );
}

const mount = document.getElementById("priniti-overlays");
if (mount) createRoot(mount).render(<Overlays />);

const cartPage = document.getElementById("priniti-cart-view");
if (cartPage) createRoot(cartPage).render(<CartView />);

initHeader();
initInteractions();
initCatalog();
initForms();
void useCartStore.getState().load();

/** Small public API for other scripts. */
declare global {
  interface Window {
    priniti?: {
      openCart: () => void;
      toast: typeof toast;
      addToCart: (input: { id: number; quantity?: number; variation?: { attribute: string; value: string }[]; name?: string }) => Promise<boolean>;
    };
  }
}

window.priniti = {
  openCart: () => useUIStore.getState().openCart(),
  toast,
  addToCart: (input) => useCartStore.getState().addItem(input),
};
