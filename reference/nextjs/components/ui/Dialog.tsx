"use client";

import { useEffect, useRef, type ReactNode } from "react";
import { cn } from "@/lib/cn";

interface DialogProps {
  open: boolean;
  onClose: () => void;
  label: string;
  className?: string;
  children: ReactNode;
}

/**
 * Thin wrapper over the native <dialog> element: focus trapping, Esc to close, inert background and
 * focus restoration come from the browser. Never put a display utility on the <dialog> itself,
 * it would override the closed state. Use an inner wrapper instead.
 */
export function Dialog({ open, onClose, label, className, children }: DialogProps) {
  const ref = useRef<HTMLDialogElement>(null);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    if (open && !el.open) el.showModal();
    else if (!open && el.open) el.close();
  }, [open]);

  return (
    <dialog
      ref={ref}
      aria-label={label}
      onClose={onClose}
      onClick={(e) => {
        if (e.target === e.currentTarget) onClose();
      }}
      className={cn("p-0 text-ink backdrop:bg-ink/50 backdrop:backdrop-blur-[2px]", className)}
    >
      {open ? children : null}
    </dialog>
  );
}
