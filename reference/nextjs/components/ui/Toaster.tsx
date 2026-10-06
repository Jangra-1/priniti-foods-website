"use client";

import { Check, Info, TriangleAlert } from "lucide-react";
import { useEffect, useRef } from "react";
import { cn } from "@/lib/cn";
import { useToastStore } from "@/store/toast";

const icons = { success: Check, info: Info, error: TriangleAlert };

/**
 * Rendered in the browser top layer via the Popover API so toasts stay visible above open drawers/modals.
 */
export function Toaster() {
  const toasts = useToastStore((s) => s.toasts);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const open = el.matches(":popover-open");
    if (toasts.length && !open) el.showPopover();
    if (!toasts.length && open) el.hidePopover();
  }, [toasts.length]);

  return (
    <div
      ref={ref}
      popover="manual"
      className="pointer-events-none fixed inset-x-0 bottom-4 top-auto m-0 flex w-full flex-col items-center gap-2 overflow-visible border-0 bg-transparent p-0 px-4"
    >
      {toasts.map((t) => {
        const Icon = icons[t.tone ?? "success"];
        return (
          <div
            key={t.id}
            role="status"
            className="pointer-events-auto flex max-w-sm animate-toast-in items-center gap-3 rounded-full bg-ink py-2.5 pl-3 pr-5 text-sm text-white shadow-lift"
          >
            <span
              className={cn(
                "flex size-6 shrink-0 items-center justify-center rounded-full",
                t.tone === "error" ? "bg-brand" : t.tone === "info" ? "bg-white/20" : "bg-leaf",
              )}
            >
              <Icon className="size-3.5" aria-hidden />
            </span>
            <span className="min-w-0">
              <span className="block font-semibold">{t.title}</span>
              {t.description ? <span className="block truncate text-white/70">{t.description}</span> : null}
            </span>
          </div>
        );
      })}
    </div>
  );
}
