import { createRoot } from "react-dom/client";
import { CartDrawer } from "@theme/components/CartDrawer";
import { MobileNavigation } from "@theme/components/MobileNavigation";
import { SearchModal } from "@theme/components/SearchModal";
import { Toaster } from "@theme/components/Toaster";
import { initHeader } from "@theme/header";
import { useCartStore } from "@theme/stores/cart";
import { toast } from "@theme/stores/toast";
import { useUIStore } from "@theme/stores/ui";

/**
 * Islands entry. PHP renders every page; this bundle mounts the global overlays that
 * reference/nextjs/app/layout.tsx renders on every page (CartDrawer, MobileNavigation, SearchModal, Toaster)
 * and wires up the header. Page-level islands (add to cart, filters, gallery...) arrive with their phases.
 */
function Overlays() {
  return (
    <>
      <MobileNavigation />
      <SearchModal />
      <CartDrawer />
      <Toaster />
    </>
  );
}

const mount = document.getElementById("priniti-overlays");
if (mount) createRoot(mount).render(<Overlays />);

initHeader();
void useCartStore.getState().load();

/** Small public API for server-rendered markup and later islands. */
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
