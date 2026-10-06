"use client";

import { Heart } from "lucide-react";
import { useHydrated } from "@/hooks/useHydrated";
import { cn } from "@/lib/cn";
import { toast } from "@/store/toast";
import { useWishlistStore } from "@/store/wishlist";

interface WishlistButtonProps {
  productId: string;
  productName: string;
  className?: string;
}

export function WishlistButton({ productId, productName, className }: WishlistButtonProps) {
  const hydrated = useHydrated();
  const inList = useWishlistStore((s) => s.ids.includes(productId));
  const toggle = useWishlistStore((s) => s.toggle);
  const active = hydrated && inList;

  return (
    <button
      type="button"
      aria-pressed={active}
      aria-label={active ? `Remove ${productName} from wishlist` : `Add ${productName} to wishlist`}
      onClick={() => {
        toggle(productId);
        toast({ title: active ? "Removed from wishlist" : "Saved to wishlist", description: productName });
      }}
      className={cn(
        "flex size-10 items-center justify-center rounded-full bg-surface/95 text-ink shadow-card transition duration-200 hover:scale-105 active:scale-95",
        className,
      )}
    >
      <Heart className={cn("size-5 transition-colors", active && "fill-brand text-brand")} aria-hidden />
    </button>
  );
}
