import Image from "next/image";
import { siteConfig } from "@/data/site";

/** Uses the real logo once siteConfig.logo is set; until then a text wordmark stands in. */
export function Logo() {
  const { logo, name } = siteConfig;
  if (logo) {
    return <Image src={logo.src} alt={name} width={logo.width} height={logo.height} priority className="h-8 w-auto sm:h-9" />;
  }
  return (
    <span className="font-display text-xl font-extrabold tracking-tight text-brand lg:text-2xl">
      Priniti<span className="text-ink"> Foods</span>
    </span>
  );
}
