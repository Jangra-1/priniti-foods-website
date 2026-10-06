"use client";

import { useState, type FormEvent } from "react";
import { FormNotice } from "@/components/account/FormNotice";
import { validateEmailOrMobile } from "@/components/account/authValidation";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";

type Errors = { orderId?: string; contact?: string };

const validateOrderId = (v: string) => (/^[A-Za-z0-9-]{4,}$/.test(v.trim()) ? "" : "Enter the order ID from your confirmation message.");

/**
 * Track-order form PREVIEW. There is no order backend or shipping integration: no lookup is made,
 * nothing is sent or stored, and no order, status, courier or date is ever shown.
 */
export function TrackOrderForm() {
  const [orderId, setOrderId] = useState("");
  const [contact, setContact] = useState("");
  const [errors, setErrors] = useState<Errors>({});
  const [notice, setNotice] = useState("");

  const onSubmit = (e: FormEvent) => {
    e.preventDefault();
    const next: Errors = { orderId: validateOrderId(orderId) || undefined, contact: validateEmailOrMobile(contact) || undefined };
    setErrors(next);
    setNotice(next.orderId || next.contact ? "" : "Order tracking will be available once the order and shipping system is connected. No lookup was made.");
  };

  return (
    <form noValidate onSubmit={onSubmit} className="flex flex-col gap-4 rounded-3xl bg-surface p-5 shadow-soft ring-1 ring-line/70 sm:p-7">
      <Input
        label="Order ID *"
        name="orderId"
        autoComplete="off"
        required
        value={orderId}
        error={errors.orderId}
        onChange={(e) => {
          setOrderId(e.target.value);
          setNotice("");
        }}
        onBlur={() => setErrors((x) => ({ ...x, orderId: validateOrderId(orderId) || undefined }))}
      />
      <Input
        label="Email or Mobile *"
        name="contact"
        autoComplete="email"
        required
        value={contact}
        error={errors.contact}
        onChange={(e) => {
          setContact(e.target.value);
          setNotice("");
        }}
        onBlur={() => setErrors((x) => ({ ...x, contact: validateEmailOrMobile(contact) || undefined }))}
      />
      <Button type="submit" size="md" className="h-12" fullWidth>
        Track Order
      </Button>
      <FormNotice message={notice} />
    </form>
  );
}
