import { authHeader, importConfig } from "./config.ts";

/**
 * Read-only REST client. Error output contains the HTTP status and WordPress error code/message only:
 * never request headers, never credentials.
 */
export class WpError extends Error {
  constructor(
    readonly status: number,
    readonly code: string,
    message: string,
  ) {
    super(`HTTP ${status} ${code}: ${message}`);
  }
}

export async function wpGet<T>(path: string): Promise<{ data: T; total: number | null }> {
  const res = await fetch(`${importConfig.baseUrl}/wp-json${path}`, { headers: { Accept: "application/json", ...authHeader() } });
  const body = await res.json().catch(() => null);
  if (!res.ok) throw new WpError(res.status, body?.code ?? "unknown", body?.message ?? res.statusText);
  const total = res.headers.get("x-wp-total");
  return { data: body as T, total: total === null ? null : Number(total) };
}

/** Fetches every page of a collection endpoint (per_page=100). */
export async function wpGetAll<T>(path: string): Promise<T[]> {
  const out: T[] = [];
  for (let page = 1; page < 100; page++) {
    const sep = path.includes("?") ? "&" : "?";
    const { data } = await wpGet<T[]>(`${path}${sep}per_page=100&page=${page}`);
    out.push(...data);
    if (data.length < 100) break;
  }
  return out;
}
