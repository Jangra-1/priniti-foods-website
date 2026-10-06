import { Facebook, Instagram, Twitter, Youtube } from "lucide-react";
import Link from "next/link";
import { footerNav } from "@/data/navigation";
import { siteConfig, type SocialIcon } from "@/data/site";
import { getCategories } from "@/lib/api/categories";
import { Container } from "./Container";
import { Logo } from "./Logo";

const socialIcons: Record<SocialIcon, typeof Instagram> = {
  instagram: Instagram,
  facebook: Facebook,
  youtube: Youtube,
  twitter: Twitter,
};

function FooterColumn({ title, links }: { title: string; links: { label: string; href: string }[] }) {
  return (
    <div>
      <h2 className="text-xs font-bold uppercase tracking-[0.16em] text-white/60">{title}</h2>
      <ul role="list" className="mt-3 flex flex-col gap-2 text-[13px] text-white/75">
        {links.map((l) => (
          <li key={l.href}>
            <Link href={l.href} className="transition-colors hover:text-white">
              {l.label}
            </Link>
          </li>
        ))}
      </ul>
    </div>
  );
}

export async function Footer() {
  const categories = await getCategories();

  return (
    <footer className="mt-8 bg-ink text-white lg:mt-10">
      <Container className="py-9 lg:py-10">
        <div className="grid gap-8 lg:grid-cols-[1.2fr_2.4fr]">
          <div className="max-w-sm">
            <Link href="/" aria-label="Priniti Foods home" className="inline-flex rounded-xl bg-white px-3 py-2">
              <Logo />
            </Link>
            <p className="mt-3 text-[13px] leading-relaxed text-white/70">
              {siteConfig.legalName} makes snacks for every moment: namkeen, chips, puffs, sweets, cookies, rusk and more.
            </p>
            <ul role="list" className="mt-4 flex gap-2 empty:hidden">
              {siteConfig.social.map((s) => {
                const Icon = socialIcons[s.icon];
                if (!s.href) return null;
                return (
                  <li key={s.label}>
                    <a
                      href={s.href}
                      aria-label={s.label}
                      className="flex size-10 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-brand"
                    >
                      <Icon className="size-[18px]" aria-hidden />
                    </a>
                  </li>
                );
              })}
            </ul>
          </div>

          <div className="grid grid-cols-2 gap-6 sm:grid-cols-4 lg:gap-8">
            <FooterColumn title="Shop" links={footerNav.shop} />
            <FooterColumn title="Categories" links={categories.map((c) => ({ label: c.name, href: `/category/${c.slug}` }))} />
            <FooterColumn title="Customer support" links={footerNav.support} />
            <FooterColumn title="Company" links={[{ label: "About us", href: "/about" }, ...footerNav.legal]} />
          </div>
        </div>

        <div className="mt-8 flex flex-col gap-2 border-t border-white/10 pt-6 text-sm text-white/60 sm:flex-row sm:items-center sm:justify-between">
          <p>
            © {new Date().getFullYear()} {siteConfig.legalName}. All rights reserved.
          </p>
          {siteConfig.fssaiLicense ? <p>FSSAI Lic. No. {siteConfig.fssaiLicense}</p> : null}
        </div>
      </Container>
    </footer>
  );
}
