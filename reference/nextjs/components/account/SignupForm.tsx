"use client";

import Link from "next/link";
import { useState, type FormEvent } from "react";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { validateEmail, validateMobile, validateName } from "@/lib/validation";
import { FormNotice } from "./FormNotice";
import { PasswordField } from "./PasswordField";
import { validateConfirm, validateNewPassword } from "./authValidation";

type Values = { name: string; email: string; mobile: string; password: string; confirm: string };
type Errors = Partial<Record<keyof Values | "terms", string>>;

const empty: Values = { name: "", email: "", mobile: "", password: "", confirm: "" };

/**
 * Signup form PREVIEW. No account is created, nothing is submitted, no user or password is stored anywhere.
 */
export function SignupForm() {
  const [v, setV] = useState<Values>(empty);
  const [terms, setTerms] = useState(false);
  const [errors, setErrors] = useState<Errors>({});
  const [notice, setNotice] = useState("");

  const check = (values: Values, accepted: boolean): Errors => {
    const e: Errors = {
      name: validateName(values.name) || undefined,
      email: validateEmail(values.email) || undefined,
      mobile: validateMobile(values.mobile) || undefined,
      password: validateNewPassword(values.password) || undefined,
      confirm: validateConfirm(values.password)(values.confirm) || undefined,
      terms: accepted ? undefined : "Please agree to the terms to continue.",
    };
    return e;
  };
  const set = (k: keyof Values, value: string) => {
    setV((x) => ({ ...x, [k]: value }));
    setNotice("");
  };
  const blur = (k: keyof Values) => setErrors((x) => ({ ...x, [k]: check(v, terms)[k] }));

  const onSubmit = (e: FormEvent) => {
    e.preventDefault();
    const next = check(v, terms);
    setErrors(next);
    setNotice(Object.values(next).some(Boolean) ? "" : "Account creation will be connected in the next integration phase. Nothing was submitted, saved or created.");
  };

  return (
    <form noValidate onSubmit={onSubmit} className="flex flex-col gap-4">
      <Input label="Full Name *" name="name" autoComplete="name" value={v.name} error={errors.name} onChange={(e) => set("name", e.target.value)} onBlur={() => blur("name")} />
      <div className="grid gap-4 sm:grid-cols-2">
        <Input label="Email *" name="email" type="email" autoComplete="email" value={v.email} error={errors.email} onChange={(e) => set("email", e.target.value)} onBlur={() => blur("email")} />
        <Input
          label="Mobile *"
          name="mobile"
          type="tel"
          inputMode="numeric"
          autoComplete="tel-national"
          maxLength={10}
          value={v.mobile}
          error={errors.mobile}
          onChange={(e) => set("mobile", e.target.value.replace(/\D/g, ""))}
          onBlur={() => blur("mobile")}
        />
      </div>
      <div className="grid gap-4 sm:grid-cols-2">
        <PasswordField label="Password *" name="password" autoComplete="new-password" hint="At least 8 characters." value={v.password} error={errors.password} onChange={(e) => set("password", e.target.value)} onBlur={() => blur("password")} />
        <PasswordField label="Confirm Password *" name="confirm" autoComplete="new-password" value={v.confirm} error={errors.confirm} onChange={(e) => set("confirm", e.target.value)} onBlur={() => blur("confirm")} />
      </div>

      <div>
        <label className="flex cursor-pointer items-start gap-2 text-sm">
          <input
            type="checkbox"
            className="mt-0.5 size-4 accent-brand"
            checked={terms}
            aria-invalid={errors.terms ? true : undefined}
            aria-describedby={errors.terms ? "terms-error" : undefined}
            onChange={(e) => {
              setTerms(e.target.checked);
              setNotice("");
              if (e.target.checked) setErrors((x) => ({ ...x, terms: undefined }));
            }}
          />
          <span>
              I agree to the{" "}
              <span aria-disabled="true" className="font-semibold text-ink-soft underline decoration-dotted underline-offset-4">
                Terms &amp; Conditions
              </span>{" "}
              and{" "}
              <span aria-disabled="true" className="font-semibold text-ink-soft underline decoration-dotted underline-offset-4">
                Privacy Policy
              </span>
              .{" "}
              <span className="ml-0.5 inline-flex rounded-full bg-lime-tint px-2 py-0.5 align-middle text-[10px] font-bold uppercase tracking-wide text-ink-soft">Pages coming soon</span>
            </span>
        </label>
        {errors.terms ? (
          <p id="terms-error" className="mt-1 text-sm text-brand">
            {errors.terms}
          </p>
        ) : null}
      </div>

      <Button type="submit" size="md" className="h-12" fullWidth>
        Create Account
      </Button>
      <FormNotice message={notice} />
      <p className="text-center text-sm text-ink-soft">
        Already have an account?{" "}
        <Link href="/login" className="font-semibold text-brand underline-offset-4 hover:underline">
          Log in
        </Link>
      </p>
    </form>
  );
}
