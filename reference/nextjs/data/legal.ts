/**
 * Legal and customer-policy pages (Phase 5): /privacy-policy, /terms-and-conditions, /shipping-policy,
 * /return-refund-policy and /cookie-policy.
 * Rules for editing this file:
 *  - business rules (timelines, eligibility, charges) come only from the business-approved policy draft;
 *    never add terms the business has not confirmed (no free-shipping threshold, no named payment gateway, no COD,
 *    no fixed refund time);
 *  - describe only what the website actually does (cookies, browser storage, forms) as implemented in the code;
 *  - company and contact details come from data/company.ts (verified); the pages render them from there.
 * After editing, run `npm run export:data` (writes theme/priniti/inc/data/legal.php).
 */
export interface PolicySubsection {
  title: string;
  paragraphs?: string[];
  bullets?: string[];
}

export interface PolicySection {
  id: string;
  title: string;
  paragraphs?: string[];
  bullets?: string[];
  subsections?: PolicySubsection[];
}

export interface Policy {
  slug: "privacy-policy" | "terms-and-conditions" | "shipping-policy" | "return-refund-policy" | "cookie-policy";
  title: string;
  metaTitle: string;
  description: string;
  summary: string;
  sections: PolicySection[];
}

export const policyMeta = {
  lastUpdated: "7 October 2026",
} as const;

/** The verified Customer Care details and unit addresses are rendered below every policy (from data/company.ts). */
const contactLine = "You can reach Customer Care (feedback and consumer complaints) by phone or email using the details below. More ways to reach us are on the Contact page.";

export const privacyPolicy: Policy = {
  slug: "privacy-policy",
  title: "Privacy Policy",
  metaTitle: "Privacy Policy | Priniti Foods",
  description: "How Priniti Foods collects, uses, shares and protects personal information on its website.",
  summary: "How we collect, use and protect your personal information when you use the Priniti Foods website.",
  sections: [
    {
      id: "about-this-policy",
      title: "About This Policy",
      paragraphs: [
        "This Privacy Policy explains how Priniti Foods Pvt. Ltd. (\"Priniti Foods\", \"we\", \"us\" or \"our\") collects, uses, shares and protects personal information when you visit our website, contact us, create an account or place an order.",
        "By using the website you agree to the handling of information described in this policy. Please also read our Terms & Conditions and Cookie Policy.",
      ],
    },
    {
      id: "information-we-collect",
      title: "Information We Collect",
      paragraphs: ["We collect the information you give us when you use the website's features:"],
      subsections: [
        {
          title: "Enquiries",
          paragraphs: [
            "When you send an enquiry through the Contact page we collect your name, email address, mobile number (optional), the enquiry type and your message.",
          ],
        },
        {
          title: "Newsletter sign-ups",
          paragraphs: ["When you subscribe to our newsletter we collect your email address."],
        },
        {
          title: "Customer accounts",
          paragraphs: [
            "When you create an account we collect your name, email address, mobile number and password. Your password is stored in encrypted (hashed) form and is never visible to us. When you log in we use your email address or mobile number and password to confirm your identity.",
          ],
        },
        {
          title: "Orders and delivery",
          paragraphs: [
            "When you place an order we collect the details needed to process and deliver it, such as your name, mobile number, email address, delivery address and pincode, the products ordered and the order value. When you track an order we use the order ID and the email address or mobile number you enter to find it.",
          ],
        },
        {
          title: "Information stored automatically",
          paragraphs: [
            "Like most websites, our web server records basic technical information for security and to keep the website running, such as your IP address, browser type and the pages requested. We also use cookies and similar browser storage, as explained in our Cookie Policy.",
          ],
        },
      ],
    },
    {
      id: "how-we-use-information",
      title: "How We Use Information",
      paragraphs: ["We use personal information to:"],
      bullets: [
        "respond to your enquiries and provide customer support",
        "create and manage your account and verify your identity when you log in",
        "process, deliver and support your orders, including order tracking",
        "send transactional messages about your account, enquiries and orders",
        "send our newsletter if you have subscribed to it",
        "protect the website and our customers against misuse, spam and fraud",
        "improve our website, products and services",
        "meet our legal, tax and regulatory obligations",
      ],
    },
    {
      id: "sharing-of-information",
      title: "Sharing of Information",
      paragraphs: ["We do not sell your personal information. We share it only when needed to run our business and serve you:"],
      bullets: [
        "with service providers who host and operate the website and deliver our emails, under obligations to protect your information",
        "with courier and delivery partners, who receive the name, address and contact details needed to deliver your order",
        "with payment service providers, when online payment is enabled, to process your payment",
        "with government or law-enforcement authorities when required by law or to protect our rights, customers or the public",
      ],
      subsections: [
        {
          title: "Links to other websites",
          paragraphs: [
            "Some links open other websites or apps, such as map links on the Contact page, social media profiles, and phone and email links. Those services have their own privacy practices, which this policy does not cover.",
          ],
        },
      ],
    },
    {
      id: "data-security",
      title: "Data Security",
      paragraphs: [
        "We use reasonable technical and organisational measures to protect personal information. Website forms are protected against automated misuse, passwords are stored in hashed form, and enquiries and subscriber details are stored privately and are accessible only to authorised staff.",
        "No method of transmission over the internet or of electronic storage is completely secure, so we cannot guarantee absolute security. Please keep your account password confidential.",
      ],
    },
    {
      id: "data-retention",
      title: "Data Retention",
      paragraphs: [
        "We keep personal information only for as long as it is needed for the purposes described in this policy, including to provide your account and orders, resolve disputes and meet legal, tax and accounting requirements. When it is no longer needed, we delete it or make it anonymous.",
      ],
    },
    {
      id: "your-choices-and-rights",
      title: "Your Choices and Rights",
      bullets: [
        "You can view and update your account details from the My Account page.",
        "You can ask us to access, correct or delete your personal information, subject to information we must keep by law.",
        "You can ask us to stop sending you the newsletter at any time.",
        "You can control cookies through your browser settings, as described in our Cookie Policy.",
      ],
      paragraphs: ["To make a request, contact Customer Care using the details on this page. We may need to verify your identity before acting on a request."],
    },
    {
      id: "children",
      title: "Children's Privacy",
      paragraphs: [
        "Our website is intended for adults. Accounts and orders should be created by a parent or guardian. We do not knowingly collect personal information from children.",
      ],
    },
    {
      id: "changes-to-this-policy",
      title: "Changes to This Policy",
      paragraphs: [
        "We may update this Privacy Policy from time to time. The updated version will be posted on this page with a new \"Last updated\" date. Please review it periodically.",
      ],
    },
    {
      id: "contact-us",
      title: "Contact Us",
      paragraphs: ["For questions, requests or complaints about this Privacy Policy or your personal information, please contact us.", contactLine],
    },
  ],
};

export const termsPolicy: Policy = {
  slug: "terms-and-conditions",
  title: "Terms & Conditions",
  metaTitle: "Terms & Conditions | Priniti Foods",
  description: "The terms that apply when you use the Priniti Foods website, create an account and place orders.",
  summary: "The terms that apply when you browse the Priniti Foods website, create an account and place an order.",
  sections: [
    {
      id: "introduction",
      title: "Introduction",
      paragraphs: [
        "These Terms & Conditions apply to your use of the Priniti Foods website, operated by Priniti Foods Pvt. Ltd. (\"Priniti Foods\", \"we\", \"us\" or \"our\"), and to orders placed through it. By using the website, creating an account or placing an order, you agree to these terms.",
        "These terms should be read together with our Privacy Policy, Shipping Policy, Return & Refund Policy and Cookie Policy.",
      ],
    },
    {
      id: "website-use",
      title: "Use of the Website",
      bullets: [
        "Use the website only for lawful purposes and in a way that does not affect other users.",
        "Provide accurate, current and complete information in forms, accounts and orders.",
        "Do not copy, reproduce or use website content for commercial purposes without our written permission.",
      ],
    },
    {
      id: "customer-accounts",
      title: "Customer Accounts",
      bullets: [
        "You can create an account with your name, email address and mobile number. You are responsible for keeping your password confidential and for activity under your account.",
        "Tell us promptly if you suspect unauthorised use of your account.",
        "We may suspend or close an account that is used in breach of these terms or for fraudulent activity.",
      ],
    },
    {
      id: "product-information",
      title: "Product Information",
      paragraphs: [
        "We make reasonable efforts to show product names, images, pack sizes and descriptions accurately. Product images are for illustration; packaging design may change from time to time.",
        "Always refer to the information printed on the product pack for ingredients, allergens, nutrition, net weight, best-before date and storage instructions. If you have a food allergy or dietary requirement, please read the pack carefully before consuming.",
      ],
    },
    {
      id: "pricing-and-mrp",
      title: "Pricing and MRP",
      bullets: [
        "All prices are in Indian Rupees (INR).",
        "MRP is the maximum retail price printed on the product pack and is inclusive of all taxes. Prices on the website do not exceed the MRP.",
        "Multi-pack offers, where shown on a product, are applied to the eligible quantity in the cart.",
        "Shipping charges are applicable to orders and are calculated and shown at checkout before you place your order.",
        "If a product is listed at an incorrect price because of an error, we may cancel the order for that product and refund any amount paid for it.",
      ],
    },
    {
      id: "product-availability",
      title: "Product Availability",
      paragraphs: [
        "All products are subject to availability. A product shown on the website may be out of stock or unavailable for some delivery locations. If a product you ordered becomes unavailable, we will inform you and refund any amount paid for it.",
      ],
    },
    {
      id: "order-acceptance",
      title: "Order Acceptance",
      paragraphs: [
        "Placing an order is an offer to buy. An order is accepted when we confirm it. We may decline or cancel an order, in full or in part, for reasons such as product unavailability, pricing errors, delivery locations that are not serviceable, incomplete or incorrect details, or suspected fraudulent or unauthorised activity. If we cancel an order after payment, the amount paid will be refunded.",
      ],
    },
    {
      id: "payment",
      title: "Payment",
      paragraphs: [
        "The payment methods available for your order will be shown at checkout when payment integration is enabled. Payments are processed by the payment service provider, subject to its own terms. We do not store your full card or bank details on our website.",
      ],
    },
    {
      id: "shipping",
      title: "Shipping",
      paragraphs: [
        "We currently deliver within India, subject to courier serviceability at the delivery address. Processing and delivery times are estimates, not guarantees. Shipping charges and delivery details are explained in our Shipping Policy.",
      ],
    },
    {
      id: "cancellation",
      title: "Cancellation",
      paragraphs: [
        "You can request cancellation of an order before it is dispatched by contacting Customer Care with your order ID. Once an order has been dispatched, cancellation cannot be guaranteed. See our Return & Refund Policy for details.",
      ],
    },
    {
      id: "returns-and-refunds",
      title: "Returns and Refunds",
      paragraphs: [
        "Because our products are food items, change-of-mind returns are not accepted. Damaged, wrong, missing or quality-affected products must be reported within 48 hours of delivery. Eligibility, the reporting process, replacements and refunds are explained in our Return & Refund Policy.",
      ],
    },
    {
      id: "intellectual-property",
      title: "Intellectual Property",
      paragraphs: [
        "The Priniti name, logos, product names, packaging designs, photographs, text and other content on this website belong to Priniti Foods Pvt. Ltd. or are used with permission. They may not be copied, reproduced, modified or used without our prior written permission.",
      ],
    },
    {
      id: "prohibited-use",
      title: "Prohibited and Fraudulent Use",
      paragraphs: ["You must not:"],
      bullets: [
        "place fake, fraudulent or unauthorised orders, or use another person's account, identity or payment details",
        "attempt to gain unauthorised access to the website, other accounts or our systems",
        "interfere with the website's operation, for example with malicious code, automated scraping or excessive requests",
        "submit false, abusive or unlawful content through forms or reviews",
      ],
      subsections: [
        {
          title: "Our response",
          paragraphs: ["We may refuse service, cancel orders, suspend accounts and take legal action where we reasonably suspect misuse or fraud."],
        },
      ],
    },
    {
      id: "limitation-of-liability",
      title: "Limitation of Liability",
      paragraphs: [
        "We work to keep the website available and accurate, but we do not guarantee that it will be uninterrupted or error-free. To the extent permitted by applicable law, Priniti Foods is not liable for indirect or consequential loss arising from the use of the website, and our liability for any order is limited to the amount paid for that order.",
        "Nothing in these terms limits any rights you have as a consumer under applicable Indian law.",
      ],
    },
    {
      id: "changes-to-terms",
      title: "Changes to These Terms",
      paragraphs: [
        "We may update these terms from time to time. The updated version will be posted on this page with a new \"Last updated\" date and applies to orders placed after it is posted.",
      ],
    },
    {
      id: "contact-information",
      title: "Contact Information",
      paragraphs: ["For questions about these terms or your order, please contact us.", contactLine],
    },
  ],
};

export const shippingPolicy: Policy = {
  slug: "shipping-policy",
  title: "Shipping Policy",
  metaTitle: "Shipping Policy | Priniti Foods",
  description: "How Priniti Foods processes, ships and delivers orders within India: timelines, charges and tracking.",
  summary: "How we process, ship and deliver your Priniti Foods orders within India.",
  sections: [
    {
      id: "delivery-locations",
      title: "Delivery Locations",
      paragraphs: [
        "We currently ship orders to addresses within India only, subject to courier serviceability at the delivery pincode. International shipping is not available.",
      ],
    },
    {
      id: "order-processing",
      title: "Order Processing",
      paragraphs: [
        "Orders are generally processed and dispatched within 1–2 working days of being placed and confirmed.",
      ],
    },
    {
      id: "delivery-time",
      title: "Estimated Delivery Time",
      paragraphs: [
        "After dispatch, delivery is generally expected within 3–7 working days, depending on the delivery location.",
        "Processing and delivery times are estimates, not guarantees.",
      ],
    },
    {
      id: "shipping-charges",
      title: "Shipping Charges",
      paragraphs: [
        "Shipping charges are applicable to orders. They are calculated based on your order and delivery location, and are shown at checkout before you place your order.",
      ],
    },
    {
      id: "order-tracking",
      title: "Order Tracking",
      paragraphs: [
        "Once your order is dispatched, tracking details will be made available where supported. You can also check your order status on the Track Order page using your order ID and the email address or mobile number used at checkout.",
      ],
    },
    {
      id: "delivery-delays",
      title: "Delivery Delays",
      paragraphs: ["Delivery may occasionally be delayed for reasons such as:"],
      bullets: [
        "courier or network disruptions",
        "weather conditions",
        "public holidays",
        "other circumstances outside our reasonable control",
      ],
    },
    {
      id: "address-details",
      title: "Incorrect or Incomplete Address",
      paragraphs: [
        "Please make sure your delivery address, pincode and mobile number are correct and complete. If an incorrect or incomplete address causes a delay, return or re-dispatch of the order, additional shipping charges may apply.",
      ],
    },
    {
      id: "receiving-your-order",
      title: "Receiving Your Order",
      paragraphs: [
        "Please check your package when it arrives. If the package or product is damaged, wrong or missing, report it to Customer Care within 48 hours of delivery, as explained in our Return & Refund Policy.",
      ],
    },
    {
      id: "contact-us",
      title: "Contact Us",
      paragraphs: ["For questions about shipping or a delivery, please contact us with your order ID.", contactLine],
    },
  ],
};

export const returnPolicy: Policy = {
  slug: "return-refund-policy",
  title: "Return & Refund Policy",
  metaTitle: "Return & Refund Policy | Priniti Foods",
  description: "How to report damaged, wrong, missing or quality-affected Priniti Foods products, and how replacements, refunds and cancellations work.",
  summary: "What to do if there is a problem with your order, and how replacements, refunds and cancellations work.",
  sections: [
    {
      id: "overview",
      title: "Overview",
      paragraphs: [
        "Our products are food items, so returns are accepted only for genuine problems with an order. This policy explains which issues are eligible, how to report them and how we resolve them.",
      ],
    },
    {
      id: "eligible-issues",
      title: "Eligible Issues",
      paragraphs: ["You can report an issue if you receive:"],
      bullets: [
        "a damaged package or product",
        "a wrong product",
        "a missing product",
        "a product with a manufacturing or quality issue",
      ],
    },
    {
      id: "reporting-window",
      title: "Reporting Window",
      paragraphs: ["Please report any delivery or product issue within 48 hours of delivery."],
    },
    {
      id: "how-to-report",
      title: "How to Report an Issue",
      paragraphs: ["Contact Customer Care and provide:"],
      bullets: [
        "your order ID",
        "a description of the issue",
        "photos or a video of the package and product, where applicable",
      ],
      subsections: [
        {
          title: "Review",
          paragraphs: ["We will review your report and may contact you for more details before deciding on a resolution."],
        },
      ],
    },
    {
      id: "non-returnable",
      title: "Non-Returnable Items",
      bullets: [
        "Opened or used food products are generally not eligible for return, unless the issue is a verified manufacturing or quality problem.",
        "Change-of-mind returns are not accepted for food and snack products.",
      ],
    },
    {
      id: "replacements-and-refunds",
      title: "Replacements and Refunds",
      paragraphs: [
        "For verified damaged, wrong, missing or quality-affected products, we may provide a replacement or a refund after review.",
        "Approved refunds are made to the original payment method. The time taken for the amount to reach your account depends on the payment provider's processing timelines.",
      ],
    },
    {
      id: "cancellation",
      title: "Order Cancellation",
      paragraphs: [
        "You can request cancellation before your order is dispatched by contacting Customer Care with your order ID. Once an order has been dispatched, cancellation cannot be guaranteed. If an order is cancelled after payment, the refund is made to the original payment method.",
      ],
    },
    {
      id: "your-rights",
      title: "Your Rights",
      paragraphs: ["Nothing in this policy limits any rights you have as a consumer under applicable Indian law."],
    },
    {
      id: "contact-us",
      title: "Contact Us",
      paragraphs: ["To report an issue or request a cancellation, please contact us with your order ID.", contactLine],
    },
  ],
};

export const cookiePolicy: Policy = {
  slug: "cookie-policy",
  title: "Cookie Policy",
  metaTitle: "Cookie Policy | Priniti Foods",
  description: "How the Priniti Foods website uses cookies and similar browser storage, and how you can control them.",
  summary: "How our website uses cookies and similar browser storage, and how you can control them.",
  sections: [
    {
      id: "what-are-cookies",
      title: "What Are Cookies",
      paragraphs: [
        "Cookies are small text files that a website stores in your browser. Websites also use similar technologies, such as your browser's local storage. They help a website work, remember your session and preferences, and improve your experience.",
        "This policy explains the cookies and similar storage used on the Priniti Foods website. It should be read with our Privacy Policy.",
      ],
    },
    {
      id: "cookies-we-use",
      title: "Cookies We Use",
      subsections: [
        {
          title: "Essential and session cookies",
          paragraphs: [
            "These are needed for the website to work, for example to keep your browsing session and to check that your browser accepts cookies when you log in. The website cannot work properly without them.",
          ],
        },
        {
          title: "Authentication and account cookies",
          paragraphs: [
            "When you log in, cookies keep you signed in as you move between pages and confirm it is you. If you choose \"Remember me\", you stay signed in for longer. These cookies are removed when you log out or when they expire.",
          ],
        },
        {
          title: "Cart and shopping cookies",
          paragraphs: [
            "Our online store uses cookies to remember the items in your cart and your checkout session, so your cart is kept as you browse and when you return.",
          ],
        },
        {
          title: "Preferences (browser storage)",
          paragraphs: [
            "The website uses your browser's local storage to remember items you have saved to your wishlist or saved for later, and your recent searches. This information stays in your browser on your device.",
          ],
        },
      ],
    },
    {
      id: "analytics-and-advertising",
      title: "Analytics and Advertising",
      paragraphs: [
        "Our website does not use analytics, advertising or social media tracking cookies. If we add such tools in future, we will update this policy first.",
      ],
    },
    {
      id: "managing-cookies",
      title: "Managing Cookies",
      paragraphs: [
        "You can view, block or delete cookies and site data in your browser settings. If you block or delete essential, account or cart cookies, some parts of the website, such as logging in, the cart and checkout, may not work properly.",
      ],
    },
    {
      id: "changes-to-this-policy",
      title: "Changes to This Policy",
      paragraphs: ["We may update this Cookie Policy from time to time. The updated version will be posted on this page with a new \"Last updated\" date."],
    },
    {
      id: "contact-us",
      title: "Contact Us",
      paragraphs: ["For questions about this Cookie Policy, please contact us.", contactLine],
    },
  ],
};
