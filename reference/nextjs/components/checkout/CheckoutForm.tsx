"use client";

import { useState, type FormEvent } from "react";
import { FormNotice } from "@/components/account/FormNotice";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { Select } from "@/components/ui/Select";
import { indianStates } from "@/data/geo";
import { formatINR } from "@/lib/format";
import { buildDraftOrder } from "@/lib/order";
import { validateEmail, validateMobile, validateName, validatePincode, validateRequired } from "@/lib/validation";
import type { CartLine } from "@/types/cart";
import type { CheckoutFieldKey, CheckoutFormValues } from "@/types/checkout";

const validators: Record<CheckoutFieldKey, (v: string) => string> = {
  fullName: validateName,
  email: validateEmail,
  mobile: validateMobile,
  addressLine1: validateRequired("your address"),
  city: validateRequired("your city"),
  state: validateRequired("your state"),
  pincode: validatePincode,
};
const fieldOrder = Object.keys(validators) as CheckoutFieldKey[];

type Errors = Partial<Record<CheckoutFieldKey, string>>;

const empty: CheckoutFormValues = { fullName: "", email: "", mobile: "", addressLine1: "", addressLine2: "", city: "", state: "", pincode: "" };

/**
 * Checkout form (development). Validates the format of the details, then prepares an in-memory order DRAFT.
 * Nothing is stored (not even in localStorage), sent anywhere, paid for or turned into a real order.
 */
export function CheckoutForm({ lines }: { lines: CartLine[] }) {
  const [values, setValues] = useState<CheckoutFormValues>(empty);
  const [errors, setErrors] = useState<Errors>({});
  const [notice, setNotice] = useState("");

  const set = (key: keyof CheckoutFormValues, value: string) => {
    setValues((v) => ({ ...v, [key]: value }));
    setNotice("");
    if (key in validators && errors[key as CheckoutFieldKey]) {
      setErrors((e) => ({ ...e, [key]: validators[key as CheckoutFieldKey](value) || undefined }));
    }
  };
  const blur = (key: CheckoutFieldKey) => setErrors((e) => ({ ...e, [key]: validators[key](values[key]) || undefined }));

  const onSubmit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const next: Errors = {};
    for (const key of fieldOrder) {
      const message = validators[key](values[key]);
      if (message) next[key] = message;
    }
    setErrors(next);

    const firstInvalid = fieldOrder.find((k) => next[k]);
    if (firstInvalid) {
      setNotice("");
      (e.currentTarget.elements.namedItem(firstInvalid) as HTMLElement | null)?.focus();
      return;
    }

    // In-memory draft only: no id, no timestamp, not persisted.
    const draft = buildDraftOrder({
      lines,
      customer: { fullName: values.fullName.trim(), email: values.email.trim(), mobile: values.mobile },
      shippingAddress: {
        addressLine1: values.addressLine1.trim(),
        addressLine2: values.addressLine2.trim() || undefined,
        city: values.city.trim(),
        state: values.state,
        pincode: values.pincode,
      },
    });
    setNotice(
      `Details look valid. A draft of this order was prepared in memory only (${draft.items.length} ${draft.items.length === 1 ? "item" : "items"}, subtotal ${formatINR(draft.subtotal)}${draft.pricingMode === "test" ? " at TEST prices" : ""}). No order was created, nothing was saved or sent, and no payment was taken.`,
    );
  };

  return (
    <form noValidate onSubmit={onSubmit} className="flex flex-col gap-8">
      <fieldset className="flex flex-col gap-4">
        <legend className="mb-1 font-display text-xl font-semibold">Customer information</legend>
        <Input label="Full name *" name="fullName" autoComplete="name" required value={values.fullName} error={errors.fullName} onChange={(e) => set("fullName", e.target.value)} onBlur={() => blur("fullName")} />
        <div className="grid gap-4 sm:grid-cols-2">
          <Input label="Email *" name="email" type="email" autoComplete="email" required value={values.email} error={errors.email} onChange={(e) => set("email", e.target.value)} onBlur={() => blur("email")} />
          <Input
            label="Mobile number *"
            name="mobile"
            type="tel"
            inputMode="numeric"
            autoComplete="tel-national"
            maxLength={10}
            required
            value={values.mobile}
            error={errors.mobile}
            onChange={(e) => set("mobile", e.target.value.replace(/\D/g, ""))}
            onBlur={() => blur("mobile")}
          />
        </div>
      </fieldset>

      <fieldset className="flex flex-col gap-4">
        <legend className="mb-1 font-display text-xl font-semibold">Shipping address</legend>
        <Input label="Address line *" name="addressLine1" autoComplete="address-line1" required value={values.addressLine1} error={errors.addressLine1} onChange={(e) => set("addressLine1", e.target.value)} onBlur={() => blur("addressLine1")} />
        <Input label="Apartment, landmark (optional)" name="addressLine2" autoComplete="address-line2" value={values.addressLine2} onChange={(e) => set("addressLine2", e.target.value)} />
        <div className="grid gap-4 sm:grid-cols-3">
          <Input label="City *" name="city" autoComplete="address-level2" required value={values.city} error={errors.city} onChange={(e) => set("city", e.target.value)} onBlur={() => blur("city")} />
          <Select label="State *" name="state" autoComplete="address-level1" required placeholder="Select state" options={indianStates} value={values.state} error={errors.state} onChange={(e) => set("state", e.target.value)} onBlur={() => blur("state")} />
          <Input
            label="Pincode *"
            name="pincode"
            inputMode="numeric"
            autoComplete="postal-code"
            maxLength={6}
            required
            value={values.pincode}
            error={errors.pincode}
            onChange={(e) => set("pincode", e.target.value.replace(/\D/g, ""))}
            onBlur={() => blur("pincode")}
          />
        </div>
      </fieldset>

      <section aria-labelledby="delivery-heading" className="rounded-card border border-dashed border-line p-5">
        <h2 id="delivery-heading" className="font-display text-xl font-semibold">
          Shipping
        </h2>
        <p className="mt-1 text-sm text-ink-soft">To be calculated. Delivery options, charges and timelines will appear here once shipping rules are configured.</p>
      </section>

      <section aria-labelledby="payment-heading" className="rounded-card border border-dashed border-line p-5">
        <h2 id="payment-heading" className="font-display text-xl font-semibold">
          Payment
        </h2>
        <p className="mt-1 text-sm text-ink-soft">Payment integration is coming next. Payment methods will appear here once a payment gateway is connected. No payment is taken on this page.</p>
      </section>

      <div className="flex flex-col gap-3">
        <Button type="submit" variant="secondary" size="lg" className="self-start">
          Check my details
        </Button>
        <FormNotice message={notice} />
      </div>
    </form>
  );
}
