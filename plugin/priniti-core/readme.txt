=== Priniti Core ===
Requires at least: 6.4
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 0.3.0
License: Proprietary

Store data and business logic for the Priniti Foods storefront.

== Description ==

* Product fields from the design's data model, edited in the "Priniti details" product tab (highlights, ingredients,
  nutrition, storage, shipping note, FAQs, pack size and source, internal notes that are never rendered).
* Product category flags: hide while empty (Combos), cover product.
* Checkout rules: single "Full name", Indian mobile and pincode validation, India only, ship to the billing address,
  no order notes, at most 10 of each item.
* Storefront forms with nonce, honeypot and rate limits: sign in with email or mobile, sign up, contact enquiries
  (stored under Enquiries and emailed; Settings > Priniti), newsletter subscribers, order tracking.
* Order statuses Packed, Shipped, Delivered for the order journey on /track-order.

Presentation lives in the "priniti" theme.
