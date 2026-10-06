const tints = ["bg-navy-tint", "bg-brand-tint", "bg-leaf-tint", "bg-lime-tint"];

/** Rotating flat tint per category so cards read as a family without needing artwork. */
export const tintForIndex = (i: number) => tints[i % tints.length];
