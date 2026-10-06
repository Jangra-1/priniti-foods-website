/** Basic format checks for the checkout form preview. Nothing is sent anywhere. */
export const validateName = (v: string) => (v.trim().length >= 2 ? "" : "Enter your full name.");
export const validateMobile = (v: string) => (/^[6-9]\d{9}$/.test(v.replace(/\s+/g, "")) ? "" : "Enter a valid 10-digit mobile number.");
export const validateEmail = (v: string) => (/^\S+@\S+\.\S+$/.test(v.trim()) ? "" : "Enter a valid email address.");
export const validateRequired = (label: string) => (v: string) => (v.trim() ? "" : `Enter ${label}.`);
export const validatePincode = (v: string) => (/^[1-9]\d{5}$/.test(v.trim()) ? "" : "Enter a valid 6-digit pincode.");
