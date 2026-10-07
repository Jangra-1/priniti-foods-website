/**
 * Multi-pack pricing for single packs (Pcs = 1): 1 pack at MRP, 2 or 3 packs at MRP x n x 0.88.
 * Mirrors priniti-core's pack-pricing.php (which prices the cart) and the theme's priniti_multipack_options().
 */
export const MULTIPACK_DISCOUNT = 0.12;

export interface PackOption {
  packs: number;
  total: number;
  mrp: number;
  off: number;
}

export function multipackOptions(unitMrp: number): PackOption[] {
  return [1, 2, 3].map((packs) => ({
    packs,
    total: packs === 1 ? unitMrp : Math.round(unitMrp * packs * (1 - MULTIPACK_DISCOUNT) * 100) / 100,
    mrp: unitMrp * packs,
    off: packs === 1 ? 0 : Math.round(MULTIPACK_DISCOUNT * 100),
  }));
}
