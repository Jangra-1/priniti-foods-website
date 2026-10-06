/**
 * Field rules, same messages as reference/nextjs/lib/validation.ts and components/account/authValidation.ts.
 * The server validates again (priniti-core); this only gives the same instant feedback as the reference.
 */
export const validateName = (v: string) => (v.trim().length >= 2 ? "" : "Enter your full name.");
export const validateMobile = (v: string) => (/^[6-9]\d{9}$/.test(v.replace(/\s+/g, "")) ? "" : "Enter a valid 10-digit mobile number.");
export const validateEmail = (v: string) => (/^\S+@\S+\.\S+$/.test(v.trim()) ? "" : "Enter a valid email address.");
export const validateRequired = (label: string) => (v: string) => (v.trim() ? "" : `Enter ${label}.`);
export const validatePincode = (v: string) => (/^[1-9]\d{5}$/.test(v.trim()) ? "" : "Enter a valid 6-digit pincode.");
export const validateEmailOrMobile = (v: string) => {
  const t = v.trim();
  if (!t) return "Enter your email or mobile number.";
  return t.includes("@") ? validateEmail(t) : validateMobile(t);
};
export const validateNewPassword = (v: string) => (v.length >= 8 ? "" : "Use at least 8 characters.");
export const validateOrderId = (v: string) => (/^#?[A-Za-z0-9-]{1,}$/.test(v.trim()) ? "" : "Enter the order ID from your confirmation message.");

/** Resolves a data-validate rule ("email", "required:your city", "confirm:<input id>", ...) to a check. */
export function ruleFor(rule: string, form: HTMLFormElement | null): ((v: string, el: HTMLInputElement) => string) | null {
  const [name, arg = ""] = rule.split(/:(.*)/s);
  switch (name) {
    case "name":
      return validateName;
    case "email":
      return validateEmail;
    case "mobile":
      return validateMobile;
    case "mobile-optional":
      return (v) => (v.trim() ? validateMobile(v) : "");
    case "pincode":
      return validatePincode;
    case "email-or-mobile":
      return validateEmailOrMobile;
    case "new-password":
      return validateNewPassword;
    case "order-id":
      return validateOrderId;
    case "minlen":
      return (v) => (v.trim().length >= Number(arg) ? "" : `Please write at least ${arg} characters.`);
    case "required":
      return validateRequired(arg || "this field");
    case "checked":
      return (_v, el) => (el.checked ? "" : arg);
    case "confirm":
      return (v) => {
        const other = form?.querySelector<HTMLInputElement>(`#${CSS.escape(arg)}`);
        return v && v === other?.value ? "" : "Passwords do not match.";
      };
    default:
      return null;
  }
}
