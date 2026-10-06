import { config } from "@theme/config";

/**
 * Minimal WooCommerce Store API client (wc/store/v1). Same-origin, cookie session; no credentials involved.
 * Writes send the Store API nonce, which WooCommerce rotates via the `Nonce` response header.
 */

export interface StoreApiCartItem {
  key: string;
  id: number;
  quantity: number;
  name: string;
  permalink: string;
  images: { src: string; thumbnail: string; alt: string }[];
  variation: { attribute: string; value: string }[];
  item_data: { name?: string; key?: string; value: string; display?: string }[];
  quantity_limits: { minimum: number; maximum: number; multiple_of: number; editable: boolean };
  prices: { price: string; regular_price: string; sale_price: string; currency_minor_unit: number };
}

export interface StoreApiCart {
  items: StoreApiCartItem[];
  items_count: number;
  totals: { total_items: string; total_items_tax: string; currency_minor_unit: number };
}

export class StoreApiError extends Error {
  constructor(
    message: string,
    readonly code: string,
  ) {
    super(message);
  }
}

let nonce = config.storeApi.nonce;

async function request<T>(path: string, init: { method?: "GET" | "POST"; body?: unknown } = {}): Promise<T> {
  const res = await fetch(`${config.storeApi.root}${path}`, {
    method: init.method ?? "GET",
    credentials: "same-origin",
    headers: {
      Accept: "application/json",
      ...(init.body !== undefined ? { "Content-Type": "application/json" } : {}),
      ...(init.method === "POST" ? { Nonce: nonce } : {}),
    },
    body: init.body !== undefined ? JSON.stringify(init.body) : undefined,
  });
  const next = res.headers.get("Nonce");
  if (next) nonce = next;
  const data = await res.json().catch(() => null);
  if (!res.ok) {
    const message = typeof data?.message === "string" ? decodeEntities(data.message) : "Something went wrong. Please try again.";
    throw new StoreApiError(message, typeof data?.code === "string" ? data.code : "unknown");
  }
  return data as T;
}

export const storeApi = {
  getCart: () => request<StoreApiCart>("cart"),
  addItem: (body: { id: number; quantity: number; variation?: { attribute: string; value: string }[] }) =>
    request<StoreApiCart>("cart/add-item", { method: "POST", body }),
  updateItem: (key: string, quantity: number) => request<StoreApiCart>("cart/update-item", { method: "POST", body: { key, quantity } }),
  removeItem: (key: string) => request<StoreApiCart>("cart/remove-item", { method: "POST", body: { key } }),
};

/** Store API returns names and messages HTML-encoded (e.g. "Cream &#8217;n&#8217; Onion"). */
export function decodeEntities(value: string): string {
  const el = document.createElement("textarea");
  el.innerHTML = value;
  return el.value;
}

/** Store API money values are strings in minor units (paise). */
export const fromMinor = (value: string | undefined, minorUnit: number) => Number(value ?? 0) / 10 ** minorUnit;
