/**
 * ONE SWITCH for development TEST prices.
 *
 *   NEXT_PUBLIC_TEST_PRICES=on   -> test prices on (any environment)
 *   NEXT_PUBLIC_TEST_PRICES=off  -> test prices off (any environment)
 *   unset                        -> on in `next dev`, OFF in production builds
 *
 * Put the variable in .env.local (see .env.example). With the switch off, no product has a price,
 * the cart cannot be filled, and any cart lines that were added with test prices are discarded.
 * Test prices live ONLY in data/test-prices.ts. They are not real Priniti prices and are never MRPs.
 */
const flag = process.env.NEXT_PUBLIC_TEST_PRICES;

export const testPricingEnabled: boolean = flag === "on" || (flag !== "off" && process.env.NODE_ENV === "development");
