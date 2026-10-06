
import { X } from "lucide-react";
import type { ReactNode } from "react";
import { cn } from "@theme/lib/cn";
import { Dialog } from "./Dialog";
import { IconButton } from "./IconButton";

export type DrawerSide = "right" | "left" | "bottom";

const shell: Record<DrawerSide, string> = {
  right: "my-0 ml-auto mr-0 h-dvh max-h-none w-full max-w-md open:animate-drawer-right",
  left: "my-0 ml-0 mr-auto h-dvh max-h-none w-full max-w-sm open:animate-drawer-left",
  bottom: "mx-0 mb-0 mt-auto w-full max-w-none rounded-t-3xl open:animate-sheet-up",
};

interface DrawerProps {
  open: boolean;
  onClose: () => void;
  title: string;
  side?: DrawerSide;
  footer?: ReactNode;
  children: ReactNode;
}

/** Side drawer on desktop/tablet, or a bottom sheet (side="bottom") for mobile filters and sort. */
export function Drawer({ open, onClose, title, side = "right", footer, children }: DrawerProps) {
  return (
    <Dialog open={open} onClose={onClose} label={title} className={shell[side]}>
      <div className={cn("flex flex-col bg-surface", side === "bottom" ? "max-h-[88dvh]" : "h-full")}>
        <div className="flex items-center justify-between border-b border-line px-4 py-2 pl-5">
          <h2 className="font-display text-lg font-semibold">{title}</h2>
          <IconButton label={`Close ${title}`} onClick={onClose}>
            <X className="size-5" />
          </IconButton>
        </div>
        <div className="flex-1 overflow-y-auto overscroll-contain">{children}</div>
        {footer ? <div className="border-t border-line bg-surface p-4">{footer}</div> : null}
      </div>
    </Dialog>
  );
}
