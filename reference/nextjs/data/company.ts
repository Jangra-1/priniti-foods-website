/**
 * VERIFIED company facts for the About and Contact pages.
 * Source: Priniti Foods' official website (About Us and Contact Us pages), as confirmed by the business.
 * Do NOT add anything here that is not on the official pages (no revenue, no order or customer counts,
 * no ratings, no awards, no extra phone numbers, emails or addresses).
 */
export const company = {
  legalName: "Priniti Foods Pvt. Ltd.",
  founded: 2009,
  managingDirector: { name: "Mr. Rajesh Garg", title: "Managing Director", experience: "30+ years of FMCG experience" },

  reach: [
    { value: "12+", label: "Countries" },
    { value: "1,100+", label: "Cities" },
    { value: "1 lakh+", label: "Outlets" },
    { value: "2,600+", label: "Distributors and clients" },
    { value: "165+", label: "Product SKUs" },
    { value: "10", label: "Categories" },
  ],

  range: { skus: "165+", categories: 10 },

  manufacturing: {
    capacity: "3,250 kg/hour",
    warehouse: "50,000+ cartons",
  },

  certifications: ["FSSC 22000", "FSSAI", "APEDA", "FDA Thailand", "FDA USA"],

  team: {
    total: "425+",
    description: "A team of over 425 experienced and qualified professionals who work around the clock to deliver the best products and services to our customers.",
    groups: [
      { value: "200+", label: "Sales professionals" },
      { value: "25+", label: "Admin team" },
      { value: "200+", label: "Operations team" },
    ],
  },

  /** Wording follows the official About Us page. */
  intro: [
    "Founded in 2009 by the visionary and passionate leader Mr. Rajesh Garg, Priniti Foods Pvt. Ltd. stands as a testament to his rich experience spanning over 30 years in the FMCG sector. Driven by innovation and a commitment to quality, Priniti Foods specialises in an array of delectable treats, including Namkeen, Potato Chips, Sticks, Popcorn, Rings, Puffs, Fryums, Bakery and Sweets.",
    "As Priniti Foods continues to expand its product line and reach, Mr. Rajesh Garg's vision remains the guiding force behind every decision, ensuring that the company stays true to its core values.",
  ],
  values: ["Quality", "Integrity", "Customer-centricity"],

  /**
   * Journey milestones, exactly as shown on the official About Us page timeline (2009 comes from the company
   * introduction). Earlier or other milestones may exist on the official page but were not visible, so none are added.
   * NOTE: the 2024 milestone's currency symbol did not render in the source screenshot; it is read as the rupee
   * sign. Confirm before launch.
   */
  milestones: [
    { year: "2009", title: "Founded", text: "Priniti Foods Pvt. Ltd. was founded." },
    { year: "2019", title: "Entered into Sweets", text: "Entered the Soanpapdi and Gift Pack segments, catering to festive and institutional demand." },
    { year: "2021", title: "Global Footprint Begins", text: "Started export operations, marking Priniti Foods' entry into international markets." },
    { year: "2022", title: "Second Manufacturing Unit", text: "Established a second manufacturing plant at Jainpur Industrial Area, Kanpur (U.P.), enabling higher output and stronger regional reach." },
    { year: "2024", title: "100 CR Milestone", text: "Priniti Foods emerged as a ₹100 Crore company, reinforcing its position as a trusted Indian snacks manufacturer." },
    { year: "2024", title: "Cookies Launch", text: "Launched the Cookies category, expanding the product portfolio beyond traditional snacks." },
    { year: "2025", title: "Global Expansion", text: "Achieved exports to more than 12+ countries, strengthening global presence and international partnerships." },
  ],

  /** Real company photographs (public/images/about). Not labelled per unit on the source page, so they are not assigned to a unit. */
  images: {
    managingDirector: { src: "/images/about/managing-director.webp", alt: "Mr. Rajesh Garg, Managing Director of Priniti Foods", width: 496, height: 563 },
    facility: { src: "/images/about/facility.webp", alt: "Priniti Foods facility with delivery trucks at the loading bays", width: 858, height: 465 },
    production: { src: "/images/about/production.webp", alt: "Packaging line and cartons inside a Priniti Foods plant", width: 858, height: 465 },
    team1: { src: "/images/about/team-1.webp", alt: "The Priniti Foods team gathered for a group photo", width: 858, height: 559 },
    team2: { src: "/images/about/team-2.webp", alt: "Members of the Priniti Foods team in a group photo", width: 858, height: 559 },
  },

  contact: {
    customerCare: { label: "Customer Care / Feedback / Consumer Complaint", phone: "+91 8222942310", tel: "+918222942310", email: "info@prinitifoods.com" },
    sales: { label: "Sales Department", phone: "+91 8222942300", tel: "+918222942300" },
    export: { label: "Export Enquiry", phone: "+91 7082005198", tel: "+917082005198", email: "Exportmanager@prinitifoods.com" },
  },

  units: [
    { name: "Unit 1", place: "Sonipat, Haryana", lines: ["Khasra No. 28/7/1, VPO Nathupur", "Sonipat, Haryana 131029"] },
    { name: "Unit 2", place: "Kanpur, Uttar Pradesh", lines: ["Plot No. G-44, Jainpur Industrial Area", "Kanpur Dehat, Uttar Pradesh 209311"] },
  ],
} as const;
