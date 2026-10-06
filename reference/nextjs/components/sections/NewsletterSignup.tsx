"use client";

import { Bell, Mail } from "lucide-react";
import { useState } from "react";
import { Container } from "@/components/layout/Container";
import { Button } from "@/components/ui/Button";
import { Input } from "@/components/ui/Input";
import { toast } from "@/store/toast";

interface NewsletterSignupProps {
  headline: string;
  copy: string;
}

/** UI only. Nothing is submitted or stored until a backend exists. */
export function NewsletterSignup({ headline, copy }: NewsletterSignupProps) {
  const [email, setEmail] = useState("");
  const [status, setStatus] = useState<"idle" | "error" | "done">("idle");

  return (
    <section aria-labelledby="newsletter-heading" className="bg-blush py-9 lg:py-12">
      <Container>
        <div className="mx-auto flex max-w-2xl flex-col items-center text-center">
          <span className="flex size-11 items-center justify-center rounded-xl bg-brand text-white shadow-soft">
            <Mail className="size-5" aria-hidden />
          </span>
          <h2 id="newsletter-heading" className="mt-4 font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
            {headline}
          </h2>
          <p className="mt-1 text-base text-ink-soft">{copy}</p>
          <form
            noValidate
            onSubmit={(e) => {
              e.preventDefault();
              if (!/^\S+@\S+\.\S+$/.test(email.trim())) {
                setStatus("error");
                return;
              }
              setStatus("done");
              setEmail("");
              toast({ title: "Demo form", description: "No email was submitted.", tone: "info" });
            }}
            className="mt-5 flex w-full flex-col gap-2"
          >
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start">
              <div className="flex-1 text-left">
                <Input
                  label="Email address"
                  hideLabel
                  type="email"
                  name="email"
                  autoComplete="email"
                  placeholder="Enter your email address"
                  value={email}
                  className="h-12 bg-surface shadow-card"
                  onChange={(e) => {
                    setEmail(e.target.value);
                    if (status !== "idle") setStatus("idle");
                  }}
                  error={status === "error" ? "Enter a valid email address." : undefined}
                />
              </div>
              <Button type="submit" size="md" className="h-12 px-6 sm:shrink-0">
                <Bell className="size-4" aria-hidden />
                Subscribe
              </Button>
            </div>
            <p role="status" className="text-sm text-ink-soft">
              {status === "done" ? "Thanks! This is a demo form, so your email was not saved." : "Demo form: nothing is saved yet."}
            </p>
          </form>
        </div>
      </Container>
    </section>
  );
}
