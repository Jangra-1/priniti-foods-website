"use client";

import Link from "next/link";
import { useState, type FormEvent } from "react";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { FormNotice } from "./FormNotice";
import { PasswordField } from "./PasswordField";
import { validateEmailOrMobile, validatePasswordEntered } from "./authValidation";

type Errors = { identifier?: string; password?: string };

/**
 * Login form PREVIEW. There is no authentication backend: nothing is submitted, no session is created,
 * and the password is never stored (not in localStorage, cookies or anywhere else).
 */
export function LoginForm() {
  const [identifier, setIdentifier] = useState("");
  const [password, setPassword] = useState("");
  const [remember, setRemember] = useState(false);
  const [errors, setErrors] = useState<Errors>({});
  const [notice, setNotice] = useState("");
  const [forgot, setForgot] = useState(false);

  const onSubmit = (e: FormEvent) => {
    e.preventDefault();
    const next: Errors = {
      identifier: validateEmailOrMobile(identifier) || undefined,
      password: validatePasswordEntered(password) || undefined,
    };
    setErrors(next);
    setNotice(next.identifier || next.password ? "" : "Sign-in will be connected in the next integration phase. Nothing was submitted and no one has been signed in.");
  };

  return (
    <form noValidate onSubmit={onSubmit} className="flex flex-col gap-4">
      <Input
        label="Email or Mobile"
        name="identifier"
        autoComplete="username"
        value={identifier}
        error={errors.identifier}
        onChange={(e) => setIdentifier(e.target.value)}
        onBlur={() => setErrors((x) => ({ ...x, identifier: validateEmailOrMobile(identifier) || undefined }))}
      />
      <PasswordField
        label="Password"
        name="password"
        autoComplete="current-password"
        value={password}
        error={errors.password}
        onChange={(e) => setPassword(e.target.value)}
        onBlur={() => setErrors((x) => ({ ...x, password: validatePasswordEntered(password) || undefined }))}
      />

      <div className="flex flex-wrap items-center justify-between gap-2 text-sm">
        <label className="flex cursor-pointer items-center gap-2">
          <input type="checkbox" className="size-4 accent-brand" checked={remember} onChange={(e) => setRemember(e.target.checked)} />
          Remember me
        </label>
        <button type="button" onClick={() => setForgot((v) => !v)} aria-expanded={forgot} className="font-semibold text-brand underline-offset-4 hover:underline">
          Forgot Password?
        </button>
      </div>
      {forgot ? <p className="rounded-xl bg-navy-tint px-4 py-3 text-sm text-ink-soft">Password reset will be available once sign-in is connected in the next integration phase.</p> : null}

      <Button type="submit" size="md" className="h-12" fullWidth>
        Login
      </Button>
      <FormNotice message={notice} />
      <p className="text-center text-sm text-ink-soft">
        Don&apos;t have an account?{" "}
        <Link href="/signup" className="font-semibold text-brand underline-offset-4 hover:underline">
          Create Account
        </Link>
      </p>
      <p className="text-center text-sm">
        <Link href="/shop" className="font-semibold text-ink-soft underline-offset-4 hover:text-brand hover:underline">
          Continue shopping
        </Link>
      </p>
    </form>
  );
}
