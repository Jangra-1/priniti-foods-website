import { Info } from "lucide-react";

/** Honest status message shown after a valid submit of a not-yet-connected form. Renders nothing when empty. */
export function FormNotice({ message }: { message: string }) {
  return (
    <div role="status" aria-live="polite">
      {message ? (
        <p className="flex items-start gap-2.5 rounded-xl bg-navy-tint px-4 py-3 text-sm leading-relaxed text-ink">
          <Info className="mt-0.5 size-4 shrink-0 text-navy" aria-hidden />
          <span>{message}</span>
        </p>
      ) : null}
    </div>
  );
}
