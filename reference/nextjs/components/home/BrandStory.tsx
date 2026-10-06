import { Container } from "@/components/layout/Container";
import { ButtonLink } from "@/components/ui/Button";
import { MediaImage } from "@/components/ui/MediaImage";
import { home } from "@/data/home";

export function BrandStory() {
  const { story } = home;
  return (
    <section aria-labelledby="story-heading" className="border-y border-line bg-navy-tint py-14 lg:py-24">
      <Container className="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
        <div className="relative mx-auto grid w-full max-w-xl grid-cols-5 gap-3 sm:gap-4">
          <MediaImage
            image={story.images[0]}
            placeholderLabel="Brand photo pending"
            sizes="(min-width:1024px) 30vw, 60vw"
            className="col-span-3 aspect-[3/4] rounded-[1.5rem] bg-surface"
          />
          <MediaImage
            image={story.images[1]}
            placeholderLabel="Brand photo pending"
            sizes="(min-width:1024px) 20vw, 40vw"
            className="col-span-2 mt-10 aspect-[3/4] rounded-[1.5rem] bg-surface sm:mt-16"
          />
        </div>
        <div className="max-w-lg">
          <h2 id="story-heading" className="font-display text-3xl font-extrabold leading-tight sm:text-4xl lg:text-5xl">
            {story.title}
          </h2>
          <div className="mt-5 flex flex-col gap-4 text-lg leading-relaxed text-ink-soft">
            {story.paragraphs.map((p) => (
              <p key={p}>{p}</p>
            ))}
          </div>
          <ButtonLink href={story.cta.href} variant="dark" size="lg" className="mt-8 w-full sm:w-auto">
            {story.cta.label}
          </ButtonLink>
        </div>
      </Container>
    </section>
  );
}
