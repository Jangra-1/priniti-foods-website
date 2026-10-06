import { useSyncExternalStore } from "react";

const subscribe = () => () => {};

/** False on the server and during hydration, true afterwards. Prevents persisted-store mismatches. */
export function useHydrated() {
  return useSyncExternalStore(subscribe, () => true, () => false);
}
