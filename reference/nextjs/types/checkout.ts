/** Checkout form data. Held in React component state only: never stored, never sent anywhere. */
export interface CustomerInfo {
  fullName: string;
  email: string;
  /** 10-digit Indian mobile number */
  mobile: string;
}

export interface ShippingAddress {
  addressLine1: string;
  addressLine2?: string;
  city: string;
  state: string;
  /** 6-digit Indian pincode */
  pincode: string;
}

export type CheckoutFieldKey = "fullName" | "email" | "mobile" | "addressLine1" | "city" | "state" | "pincode";

/** Flat form values, split into CustomerInfo + ShippingAddress when an order draft is built. */
export type CheckoutFormValues = CustomerInfo & ShippingAddress & { addressLine2: string };
