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

/**
 * Write request (POST/PUT). Only used by `apply`, which the owner approved for the live site.
 * Same error policy as wpGet: status + WordPress code/message only.
 */
export async function wpSend<T>(
  method: "POST" | "PUT",
  path: string,
  body: unknown,
  opts: { raw?: { bytes: Uint8Array; contentType: string; filename: string } } = {},
): Promise<T> {
  const headers: Record<string, string> = { Accept: "application/json", ...authHeader() };
  let payload: BodyInit;
  if (opts.raw) {
    headers["Content-Type"] = opts.raw.contentType;
    headers["Content-Disposition"] = `attachment; filename="${opts.raw.filename}"`;
    payload = new Blob([new Uint8Array(opts.raw.bytes)]);
  } else {
    headers["Content-Type"] = "application/json";
    payload = JSON.stringify(body);
  }
  const res = await fetch(`${importConfig.baseUrl}/wp-json${path}`, { method, headers, body: payload });
  const data = await res.json().catch(() => null);
  if (!res.ok) throw new WpError(res.status, data?.code ?? "unknown", data?.message ?? res.statusText);
  return data as T;
}
