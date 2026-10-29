# iThink Logistics integration

Server-side integration with the iThink Logistics API v3 ([docs](https://docs.ithinklogistics.com/index/3)), inside `priniti-core`. **It is off by default.** Nothing calls iThink, and nothing changes on the storefront or checkout, until `PRINITI_ITHINK_ENABLED` is set to `true` in `wp-config.php`.

## What it does

| Area | Behaviour |
| --- | --- |
| Configuration | `wp-config.php` constants only. Settings > Priniti lists which are set, never their values. "Test connection" makes read-only calls (Get Warehouse, Get Store) to check the credentials, pickup address and store IDs. |
| Shipping data | Products and variations get a **Verified packed weight (g)** field (Shipping tab / variation panel). The product list has a **Shipping data** column that flags missing weights. Net weight (`_priniti_weight`) is never used as a shipping weight. |
| Shipments | Order screen box **iThink shipment**, for shop managers (`manage_woocommerce`) on Processing or Packed orders. The manager enters or confirms the weighed parcel (g) and box (cm), ticks a confirmation, and confirms a dialog. One shipment per order. Then: Refresh tracking, Get label, Cancel shipment. |
| Duplicates | Refused when the order has an AWB or a request in progress; an atomic lock stops double clicks; the state is saved before the request is sent. If iThink gives no usable answer (timeout, 5xx, bad JSON), the order is marked **uncertain**: a retry needs a tick confirming the iThink panel shows no shipment for that order number. Create and cancel are never retried automatically. |
| Order status | A new shipment moves Processing to Packed. "Refresh tracking" moves Packed to Shipped to Delivered, only ever forwards. Cancelled, RTO, reverse, lost and unknown statuses add an order note and change nothing. |
| Track Order | `/track-order` shows courier, AWB, courier status, expected delivery and the courier's tracking link for orders with a shipment. Tracking older than 30 minutes is refreshed (status text only). With the integration off, the page is unchanged. |
| Checkout rates | Shipping method **iThink courier rate**. It does nothing until someone adds it to a shipping zone. It quotes the cheapest prepaid courier for the pincode and the cart's verified weight, caches for 30 minutes, falls back to an optional fixed cost if iThink is unreachable or a weight is missing, and offers no rate if iThink quotes no courier. Optional free-delivery threshold. |
| Logging | WooCommerce > Status > Logs, source `priniti-ithink`. Errors are always logged; request bodies only with `PRINITI_ITHINK_DEBUG`. Credentials, names, addresses, phone numbers and emails are removed, and pincodes are cut to 3 digits. |

Code: `plugin/priniti-core/includes/ithink.php` (loader) and `includes/ithink/`. Tests: `tests/php/` (`npm run test:php`), which fake the API and never use the network.

## Configuration (`wp-config.php`)

Add these lines above `/* That's all, stop editing! */` on the **staging** site. Never commit them, paste them into chat, or store them in the database.

```php
// iThink Logistics (docs/ITHINK.md in the theme repository). Staging values only on the staging site.
define( 'PRINITI_ITHINK_ENABLED', true );
define( 'PRINITI_ITHINK_ENV', 'staging' );                 // 'staging' (pre-alpha) or 'production'
define( 'PRINITI_ITHINK_ACCESS_TOKEN', 'paste-new-staging-token-here' );
define( 'PRINITI_ITHINK_SECRET_KEY', 'paste-new-staging-secret-here' );
define( 'PRINITI_ITHINK_STORE_ID', 34463 );
define( 'PRINITI_ITHINK_PICKUP_ADDRESS_ID', 1293 );
define( 'PRINITI_ITHINK_RETURN_ADDRESS_ID', 0 );           // REQUIRED for shipments: ask iThink (may equal the pickup ID)
define( 'PRINITI_ITHINK_ORIGIN_PINCODE', '' );             // pincode of pickup address 1293, needed for rates
define( 'PRINITI_ITHINK_WEIGHT_UNIT', '' );                // 'kg' or 'g': REQUIRED, confirm with iThink first
// Optional:
// define( 'PRINITI_ITHINK_LOGISTICS', 'delhivery' );      // force one courier; unset = iThink assigns
// define( 'PRINITI_ITHINK_S_TYPE', 'surface' );           // 'surface' or 'air' (Delhivery/Bluedart)
// define( 'PRINITI_ITHINK_DEFAULT_BOX_CM', '30x20x15' );  // pre-fills the box size on the order screen
// define( 'PRINITI_ITHINK_TIMEOUT', 15 );                 // seconds, 3-60
// define( 'PRINITI_ITHINK_DEBUG', true );                 // log redacted request bodies
```

Production additionally needs `PRINITI_ITHINK_ENV = 'production'` **and** `define( 'PRINITI_ITHINK_ALLOW_PRODUCTION', true );`, so a copied staging config cannot reach production by accident.

| Constant | Needed for | Notes |
| --- | --- | --- |
| `PRINITI_ITHINK_ENABLED` | everything | `true` turns the module on. Anything else = off. |
| `PRINITI_ITHINK_ENV` | everything | `staging` (default) uses `pre-alpha.ithinklogistics.com`. |
| `PRINITI_ITHINK_ACCESS_TOKEN`, `PRINITI_ITHINK_SECRET_KEY` | every call | From iThink. Use **newly rotated** staging values. |
| `PRINITI_ITHINK_PICKUP_ADDRESS_ID` | shipments | 1293 (supplied by iThink). "Test connection" checks it exists. |
| `PRINITI_ITHINK_RETURN_ADDRESS_ID` | shipments | Required by Add Order. Not supplied yet. |
| `PRINITI_ITHINK_WEIGHT_UNIT` | shipments | Add Order says kg but its sample weight is `400`. No default on purpose. |
| `PRINITI_ITHINK_ORIGIN_PINCODE` | rates | Get Rate's `from_pincode`. |
| `PRINITI_ITHINK_STORE_ID` | optional | 34463. Sent as `store_id` on shipments and checked by "Test connection". |

## First staging test

Do this on the Hostinger **staging copy**, never on the live site.

1. Ask iThink to **rotate the staging token and secret** (the earlier ones were shared in chat), and to answer the open questions below. At minimum you need the return address ID and the weight unit.
2. Deploy this branch's plugin to staging only (`docs/DEPLOYMENT.md`) and activate it.
3. Edit staging `wp-config.php` through hPanel File Manager or SSH, and add the block above with the new staging values.
4. Settings > Priniti > **Test connection (staging, read-only)**. Expect "Credentials accepted. Pickup address 1293 found (pincode …). Store 34463 found". Put that pincode in `PRINITI_ITHINK_ORIGIN_PINCODE`.
5. Products: enter **Verified packed weight (g)** for the product you will test with (weigh it).
6. Create a **test order by hand** in WooCommerce > Orders > Add order. Use your own name with "TEST" in it, your own phone, and a serviceable address. Add the test product and set status **Processing**. Never use a real customer's order. Payment settings stay as they are.
7. Open the order. In **iThink shipment**, check the parcel weight and box, tick the confirmation, click **Create shipment** and confirm. **This books a staging shipment with iThink.**
8. Expect a notice with the AWB, an order note, and status **Packed**. Then try Get label (the PDF opens), Refresh tracking, and `/track-order` with the order number and your phone or email.
9. Click **Create shipment** again (it should be refused), then **Cancel shipment**.
10. Check WooCommerce > Status > Logs (`priniti-ithink`): no token, secret, name, address, phone or email may appear.

If any step fails, the order note and notice carry iThink's (redacted) message. Do not retry an **uncertain** order until the iThink panel has been checked.

## Open questions for iThink (from the v3 docs)

- **Weight unit** for Add Order `weight`: kg per the comment, but the sample is `400`.
- Get Rate types `shipping_weight_kg` as an integer. We send exact decimals (0.45 for 450 g) and never round. Does the API accept them?
- **Track production host** is documented as `api.ithinklogistics.com`, unlike every other endpoint (`my.ithinklogistics.com`).
- Get Rate's `to_pincode` is in the sample but not the field table.
- Does `rate` include GST and COD charges? What unit is `delivery_tat`?
- Which `logistics` values are enabled on this account, and should `s_type` be surface for syrup sweets (Gulab Jamun, Rasgulla)? Which products can couriers not carry?
- Does iThink reject a second Add Order with the same `order` number (server-side idempotency)?
- Is there a tracking webhook? Tracking is fetched on demand until one is confirmed.
- Does iThink's "store" 34463 also import WooCommerce orders by itself? If so, turn that off or shipments may be duplicated.
- Rate limits, error formats, and how long label links stay valid.

## Not included yet

- Automatic shipment creation (deliberately manual in this version), bulk actions, manifests from the UI, and scheduled tracking sync (tracking refreshes from the order screen and `/track-order`).
- Automatically cancelling the courier booking when an order is cancelled. An order note reminds the team instead.
- COD (no payment gateway is enabled). The code sends `payment_mode` explicitly, because iThink treats a missing mode as COD.
