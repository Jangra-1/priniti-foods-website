import { validateEmail, validateMobile } from "@/lib/validation";

/** Format checks only. Nothing is sent or stored anywhere. */
export const validateEmailOrMobile = (v: string) => {
  const t = v.trim();
  if (!t) return "Enter your email or mobile number.";
  return t.includes("@") ? validateEmail(t) : validateMobile(t);
};

export const validatePasswordEntered = (v: string) => (v ? "" : "Enter your password.");

export const validateNewPassword = (v: string) => (v.length >= 8 ? "" : "Use at least 8 characters.");

export const validateConfirm = (password: string) => (v: string) => (v && v === password ? "" : "Passwords do not match.");
