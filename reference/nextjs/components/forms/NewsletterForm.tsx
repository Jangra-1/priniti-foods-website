"use client";

import { useState } from "react";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";

/** UI only: validates the address and shows a confirmation. Nothing is sent or stored. */
export function NewsletterForm() {
  const [status, setStatus] = useState<"idle" | "error" | "done">("idle");

  return (
    <form
      noValidate
      onSubmit={(e) => {
        e.preventDefault();
        const form = e.currentTarget;
        const email = String(new FormData(form).get("email") ?? "").trim();
        if (!/^\S+@\S+\.\S+$/.test(email)) {
          setStatus("error");
          return;
        }
        setStatus("done");
        form.reset();
      }}
    >
      <div className="flex flex-col gap-2 sm:flex-row sm:items-start">
        <div className="sm:flex-1">
          <Input
            label="Email address"
            hideLabel
            id="newsletter-email"
            name="email"
            type="email"
            autoComplete="email"
            placeholder="Enter your email"
            aria-invalid={status === "error"}
            aria-describedby="newsletter-message"
          />
        </div>
        <Button type="submit" size="lg" className="h-12 sm:w-auto">
          Subscribe
        </Button>
      </div>
      <p id="newsletter-message" role="status" className="mt-2 min-h-5 text-sm text-white/75">
        {status === "error" ? "Enter a valid email address." : status === "done" ? "Demo only: nothing was submitted or saved." : ""}
      </p>
    </form>
  );
}
