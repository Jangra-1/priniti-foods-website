"use client";

import { Info } from "lucide-react";
import { useState, type FormEvent } from "react";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Select } from "@/components/ui/Select";
import { Textarea } from "@/components/ui/Textarea";
import { validateEmail, validateMobile, validateName, validateRequired } from "@/lib/validation";

const enquiryTypes = ["General Enquiry", "Product Enquiry", "Distributor Enquiry", "Export Enquiry"] as const;

type Values = { name: string; email: string; phone: string; type: string; message: string };
type Errors = Partial<Record<keyof Values, string>>;

const empty: Values = { name: "", email: "", phone: "", type: "", message: "" };

const check = (v: Values): Errors => ({
  name: validateName(v.name) || undefined,
  email: validateEmail(v.email) || undefined,
  phone: v.phone.trim() ? validateMobile(v.phone) || undefined : undefined, // phone is optional
  type: validateRequired("an enquiry type")(v.type) || undefined,
  message: v.message.trim().length >= 10 ? undefined : "Please write at least 10 characters.",
});

/**
 * Contact form PREVIEW. Nothing is sent, stored or emailed: there is no backend yet.
 * Customers can reach the team right now using the verified phone numbers and emails on this page.
 */
export function ContactForm() {
  const [v, setV] = useState<Values>(empty);
  const [errors, setErrors] = useState<Errors>({});
  const [notice, setNotice] = useState("");

  const set = (k: keyof Values, value: string) => {
    setV((x) => ({ ...x, [k]: value }));
    setNotice("");
  };
  const blur = (k: keyof Values) => setErrors((x) => ({ ...x, [k]: check(v)[k] }));

  const onSubmit = (e: FormEvent) => {
    e.preventDefault();
    const next = check(v);
    setErrors(next);
    setNotice(Object.values(next).some(Boolean) ? "" : "Contact submission will be connected in the next integration phase. Nothing was sent. For anything urgent, please call or email us using the details on this page.");
  };

  return (
    <form noValidate onSubmit={onSubmit} className="flex flex-col gap-4 rounded-3xl bg-surface p-5 shadow-soft ring-1 ring-line/70 sm:p-6">
      <div>
        <h2 className="font-display text-xl font-extrabold">Send an Enquiry</h2>
        <p className="mt-0.5 text-sm text-ink-soft">Fields marked * are required. This form does not send messages yet.</p>
      </div>
      <div className="grid gap-4 sm:grid-cols-2">
        <Input label="Full Name *" name="name" autoComplete="name" required value={v.name} error={errors.name} onChange={(e) => set("name", e.target.value)} onBlur={() => blur("name")} />
        <Input label="Email *" name="email" type="email" autoComplete="email" required value={v.email} error={errors.email} onChange={(e) => set("email", e.target.value)} onBlur={() => blur("email")} />
      </div>
      <div className="grid gap-4 sm:grid-cols-2">
        <Input
          label="Phone"
          name="phone"
          type="tel"
          inputMode="numeric"
          autoComplete="tel-national"
          maxLength={10}
          value={v.phone}
          error={errors.phone}
          onChange={(e) => set("phone", e.target.value.replace(/\D/g, ""))}
          onBlur={() => blur("phone")}
        />
        <Select label="Enquiry Type *" name="type" required placeholder="Select enquiry type" options={enquiryTypes} value={v.type} error={errors.type} onChange={(e) => set("type", e.target.value)} onBlur={() => blur("type")} />
      </div>
      <Textarea label="Message *" name="message" required value={v.message} error={errors.message} onChange={(e) => set("message", e.target.value)} onBlur={() => blur("message")} />
      <Button type="submit" size="md" className="h-12 self-start px-8">
        Send Enquiry
      </Button>
      <div role="status" aria-live="polite">
        {notice ? (
          <p className="flex items-start gap-2.5 rounded-xl bg-navy-tint px-4 py-3 text-sm leading-relaxed text-ink">
            <Info className="mt-0.5 size-4 shrink-0 text-navy" aria-hidden />
            <span>{notice}</span>
          </p>
        ) : null}
      </div>
    </form>
  );
}
