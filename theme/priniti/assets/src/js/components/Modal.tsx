
import { X } from "lucide-react";
import type { ReactNode } from "react";
import { Dialog } from "./Dialog";
import { IconButton } from "./IconButton";

interface ModalProps {
  open: boolean;
  onClose: () => void;
  title: string;
  children: ReactNode;
}

export function Modal({ open, onClose, title, children }: ModalProps) {
  return (
    <Dialog
      open={open}
      onClose={onClose}
      label={title}
      className="m-0 mt-auto max-h-[92dvh] w-full max-w-none overflow-hidden rounded-b-none rounded-t-3xl open:animate-sheet-up sm:m-auto sm:max-h-[90dvh] sm:w-[min(92vw,44rem)] sm:rounded-3xl sm:open:animate-pop"
    >
      <div className="flex max-h-[92dvh] flex-col bg-surface sm:max-h-[90dvh]">
        <div className="flex items-center justify-between border-b border-line px-4 py-2 pl-6">
          <h2 className="font-display text-lg font-semibold">{title}</h2>
          <IconButton label={`Close ${title}`} onClick={onClose}>
            <X className="size-5" />
          </IconButton>
        </div>
        <div className="overflow-y-auto overscroll-contain p-6">{children}</div>
      </div>
    </Dialog>
  );
}
