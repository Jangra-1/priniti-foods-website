import { create } from "zustand";

export interface ToastItem {
  id: string;
  title: string;
  description?: string;
  tone?: "success" | "info" | "error";
}

interface ToastState {
  toasts: ToastItem[];
  push: (toast: Omit<ToastItem, "id">) => void;
  dismiss: (id: string) => void;
}

export const useToastStore = create<ToastState>()((set, get) => ({
  toasts: [],
  push: (toast) => {
    const id = crypto.randomUUID();
    set((s) => ({ toasts: [...s.toasts.slice(-2), { ...toast, id }] }));
    setTimeout(() => get().dismiss(id), 3200);
  },
  dismiss: (id) => set((s) => ({ toasts: s.toasts.filter((t) => t.id !== id) })),
}));

/** Call from anywhere in client code: toast({ title: "Saved" }) */
export const toast = (t: Omit<ToastItem, "id">) => useToastStore.getState().push(t);
