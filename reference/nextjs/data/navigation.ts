export interface NavLink {
  label: string;
  href: string;
}

export interface NavItem extends NavLink {
  /** Desktop shows a category mega-menu under this item. */
  hasCategoryMenu?: boolean;
}

const links = {
  shop: { label: "Shop", href: "/shop" },
  about: { label: "About", href: "/about" },
  contact: { label: "Contact", href: "/contact" },
} satisfies Record<string, NavLink>;

export const desktopNav: NavItem[] = [
  links.shop,
  { label: "Categories", href: "/shop", hasCategoryMenu: true },
  links.about,
  links.contact,
];

export const mobileNav = {
  shop: [links.shop],
  info: [links.about, links.contact, { label: "Track order", href: "/track-order" }],
};

export const footerNav = {
  shop: [links.shop],
  support: [
    links.contact,
    { label: "Track order", href: "/track-order" },
    { label: "Shipping Policy", href: "/shipping-policy" },
    { label: "Return Policy", href: "/return-policy" },
  ],
  legal: [
    { label: "Privacy Policy", href: "/privacy-policy" },
    { label: "Terms & Conditions", href: "/terms" },
  ],
} satisfies Record<string, NavLink[]>;
