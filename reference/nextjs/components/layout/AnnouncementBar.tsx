import { siteConfig } from "@/data/site";

export function AnnouncementBar() {
  if (!siteConfig.announcement) return null;
  return (
    <div className="bg-ink px-4 py-1 text-center text-xs font-medium leading-5 text-white">
      <p>{siteConfig.announcement}</p>
    </div>
  );
}
