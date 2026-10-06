import { useId, type TextareaHTMLAttributes } from "react";
import { cn } from "@/lib/cn";

interface TextareaProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
  label: string;
  error?: string;
}

export function Textarea({ label, error, id, className, rows = 4, ...props }: TextareaProps) {
  const auto = useId();
  const fieldId = id ?? auto;
  return (
    <div className="flex flex-col gap-1.5">
      <label htmlFor={fieldId} className="text-sm font-medium">
        {label}
      </label>
      <textarea
        id={fieldId}
        rows={rows}
        aria-invalid={error ? true : undefined}
        aria-describedby={error ? `${fieldId}-error` : undefined}
        className={cn("w-full rounded-xl border bg-surface px-4 py-3 text-base", error ? "border-brand" : "border-line focus:border-ink", className)}
        {...props}
      />
      {error ? (
        <p id={`${fieldId}-error`} className="text-sm text-brand">
          {error}
        </p>
      ) : null}
    </div>
  );
}
