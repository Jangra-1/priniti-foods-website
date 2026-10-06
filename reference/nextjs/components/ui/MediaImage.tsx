import Image from "next/image";
import { cn } from "@/lib/cn";
import type { ImageAsset } from "@/types/product";
import { ImagePlaceholder } from "./ImagePlaceholder";

interface MediaImageProps {
  image?: ImageAsset;
  placeholderLabel?: string;
  sizes: string;
  priority?: boolean;
  className?: string;
  imageClassName?: string;
}

/** Fills its box with a real image when supplied, otherwise the labelled placeholder. */
export function MediaImage({ image, placeholderLabel, sizes, priority, className, imageClassName }: MediaImageProps) {
  return (
    <div className={cn("relative overflow-hidden", className)}>
      {image ? (
        <Image src={image.src} alt={image.alt} fill sizes={sizes} priority={priority} className={cn("object-cover", imageClassName)} />
      ) : (
        <ImagePlaceholder label={placeholderLabel} />
      )}
    </div>
  );
}
