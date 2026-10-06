"use client";

import { priceRanges, ratingOptions } from "@/lib/catalog";
import type { CatalogCapabilities, CatalogFilters } from "@/types/catalog";
import type { Category } from "@/types/category";

interface FilterFieldsProps {
  filters: CatalogFilters;
  categories: Category[];
  showCategories: boolean;
  capabilities: CatalogCapabilities;
  onChange: (patch: Partial<CatalogFilters>) => void;
  idPrefix: string;
}

const legend = "mb-2 font-display text-sm font-semibold";
const row = "flex min-h-9 cursor-pointer items-center gap-2.5 text-sm";
const control = "size-4 accent-brand";

/** Shared by the desktop sidebar (applies instantly) and the mobile sheet (applies on confirm). */
export function FilterFields({ filters, categories, showCategories, capabilities, onChange, idPrefix }: FilterFieldsProps) {
  return (
    <div className="flex flex-col gap-6">
      {showCategories ? (
        <fieldset>
          <legend className={legend}>Category</legend>
          {categories.map((c) => (
            <label key={c.slug} className={row}>
              <input
                type="checkbox"
                className={control}
                checked={filters.categories.includes(c.slug)}
                onChange={(e) =>
                  onChange({
                    categories: e.target.checked ? [...filters.categories, c.slug] : filters.categories.filter((s) => s !== c.slug),
                  })
                }
              />
              {c.name}
            </label>
          ))}
        </fieldset>
      ) : null}

      {capabilities.prices ? (
      <fieldset>
        <legend className={legend}>Price</legend>
        <label className={row}>
          <input type="radio" name={`${idPrefix}-price`} className={control} checked={!filters.price} onChange={() => onChange({ price: undefined })} />
          Any price
        </label>
        {priceRanges.map((r) => (
          <label key={r.value} className={row}>
            <input type="radio" name={`${idPrefix}-price`} className={control} checked={filters.price === r.value} onChange={() => onChange({ price: r.value })} />
            {r.label}
          </label>
        ))}
      </fieldset>
      ) : null}

      {capabilities.ratings ? (
      <fieldset>
        <legend className={legend}>Rating</legend>
        <label className={row}>
          <input type="radio" name={`${idPrefix}-rating`} className={control} checked={!filters.rating} onChange={() => onChange({ rating: undefined })} />
          Any rating
        </label>
        {ratingOptions.map((r) => (
          <label key={r} className={row}>
            <input type="radio" name={`${idPrefix}-rating`} className={control} checked={filters.rating === r} onChange={() => onChange({ rating: r })} />
            {r} stars and up
          </label>
        ))}
      </fieldset>
      ) : null}

      {capabilities.stock ? (
      <fieldset>
        <legend className={legend}>Availability</legend>
        <label className={row}>
          <input type="checkbox" className={control} checked={filters.inStock} onChange={(e) => onChange({ inStock: e.target.checked })} />
          In stock only
        </label>
      </fieldset>
      ) : null}
    </div>
  );
}
