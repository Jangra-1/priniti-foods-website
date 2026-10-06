import { ruleFor } from "@theme/lib/validation";

/**
 * Reference form behaviour for server-rendered forms ([data-priniti-form]): validate on blur, re-validate as the
 * user types once a field has an error, and on submit block, show every error and focus the first invalid field.
 * Also: digits-only inputs (mobile, pincode) and the password show/hide toggle.
 */

type Field = HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;

const split = (v: string | undefined) => (v ?? "").split(/\s+/).filter(Boolean);

function setError(el: Field, message: string) {
  const id = el.id;
  const error = id ? document.querySelector<HTMLElement>(`[data-error-for="${CSS.escape(id)}"]`) : null;
  const hint = id ? document.querySelector<HTMLElement>(`[data-hint-for="${CSS.escape(id)}"]`) : null;
  if (error) {
    error.textContent = message;
    error.hidden = !message;
  }
  if (hint) hint.hidden = !!message;
  if (message) {
    el.setAttribute("aria-invalid", "true");
    if (error) el.setAttribute("aria-describedby", error.id);
  } else {
    el.removeAttribute("aria-invalid");
    if (hint) el.setAttribute("aria-describedby", hint.id);
  }
  const bad = split(el.dataset.errorClass);
  const ok = split(el.dataset.okClass);
  el.classList.remove(...(message ? ok : bad));
  el.classList.add(...(message ? bad : ok));
}

function check(el: Field, form: HTMLFormElement): string {
  const rule = el.dataset.validate;
  if (!rule) return "";
  const fn = ruleFor(rule, form);
  return fn ? fn(el.value, el as HTMLInputElement) : "";
}

export function initForms() {
  document.querySelectorAll<HTMLFormElement>("form[data-priniti-form]").forEach((form) => {
    const fields = () => Array.from(form.querySelectorAll<Field>("[data-validate]"));

    fields().forEach((el) => {
      el.addEventListener("blur", () => setError(el, check(el, form)));
      const live = () => {
        if (el.getAttribute("aria-invalid") === "true") setError(el, check(el, form));
      };
      el.addEventListener("input", live);
      el.addEventListener("change", () => {
        if (el instanceof HTMLInputElement && el.type === "checkbox") setError(el, check(el, form));
        else live();
      });
    });

    form.addEventListener("submit", (e) => {
      let first: Field | null = null;
      for (const el of fields()) {
        const message = check(el, form);
        setError(el, message);
        if (message && !first) first = el;
      }
      if (first) {
        e.preventDefault();
        first.focus();
      }
    });
  });

  // Digits only (mobile numbers, pincodes), like the reference's onChange replace(/\D/g, "").
  document.addEventListener("input", (e) => {
    const el = e.target as HTMLInputElement;
    if (el?.matches?.("input[data-digits]")) {
      const digits = el.value.replace(/\D/g, "");
      if (digits !== el.value) el.value = digits;
    }
  });

  // Password show/hide.
  document.addEventListener("click", (e) => {
    const btn = (e.target as Element | null)?.closest<HTMLButtonElement>("[data-password-toggle]");
    if (!btn) return;
    const input = document.getElementById(btn.dataset.passwordToggle ?? "") as HTMLInputElement | null;
    if (!input) return;
    const show = input.type === "password";
    input.type = show ? "text" : "password";
    btn.setAttribute("aria-pressed", String(show));
    btn.setAttribute("aria-label", show ? "Hide password" : "Show password");
    btn.querySelector<HTMLElement>('[data-when="hidden"]')!.hidden = show;
    btn.querySelector<HTMLElement>('[data-when="shown"]')!.hidden = !show;
  });
}
