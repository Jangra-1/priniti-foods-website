export interface IntegrationItem {
  id: string;
  label: string;
  detail: string;
}

/** Everything that must exist before checkout can go live. All are PENDING. */
export const pendingIntegrations: IntegrationItem[] = [
  { id: "pricing", label: "Product pricing", detail: "Real MRP and selling prices have not been supplied for any product." },
  { id: "shipping", label: "Shipping rules and rates", detail: "Delivery areas, charges and any free-shipping rule are not configured." },
  { id: "tax", label: "Tax / GST configuration", detail: "GST rates and invoice rules are not configured." },
  { id: "payment", label: "Payment gateway", detail: "No gateway is connected, and no payment is taken or simulated." },
  { id: "orders", label: "Order backend and database", detail: "Orders cannot be created, stored or tracked yet." },
  { id: "notifications", label: "Order confirmation (email / WhatsApp)", detail: "No confirmation message can be sent yet." },
];
