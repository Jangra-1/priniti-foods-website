import { Instagram } from "lucide-react";
import { Section } from "@/components/layout/Section";
import { home } from "@/data/home";
import { siteConfig } from "@/data/site";
import { categoryTint } from "@/lib/categoryTint";
import { cn } from "@/lib/cn";

const tileSeeds = ["namkeen", "chips", "puffs-rings", "popcorn", "sweets", "cookies"];

export function SocialGrid() {
  const instagram = siteConfig.social.find((s) => s.icon === "instagram");
  return (
    <Section
      id="social"
      title={home.social.title}
      description={home.social.text}
      href={instagram?.href}
      linkLabel="Follow on Instagram"
      tone="surface"
    >
      <ul role="list" className="grid grid-cols-3 gap-2 sm:gap-3 md:grid-cols-6">
        {Array.from({ length: home.social.tiles }, (_, i) => (
          <li
            key={i}
            role="img"
            aria-label={`Instagram post placeholder ${i + 1}`}
            className={cn("relative flex aspect-square items-center justify-center overflow-hidden rounded-2xl", categoryTint(tileSeeds[i % tileSeeds.length]).bg)}
          >
            <Instagram className="size-6 text-ink/40" aria-hidden />
          </li>
        ))}
      </ul>
    </Section>
  );
}
