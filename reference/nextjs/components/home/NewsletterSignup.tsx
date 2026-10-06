import { Container } from "@/components/layout/Container";
import { NewsletterForm } from "@/components/forms/NewsletterForm";
import { home } from "@/data/home";

export function NewsletterSignup() {
  const { newsletter } = home;
  return (
    <section aria-labelledby="newsletter-heading" className="py-12 lg:py-20">
      <Container>
        <div className="grid items-center gap-8 rounded-[2rem] bg-ink p-8 text-white sm:p-12 lg:grid-cols-2 lg:gap-16 lg:p-16">
          <div>
            <h2 id="newsletter-heading" className="font-display text-3xl font-extrabold leading-tight sm:text-4xl lg:text-5xl">
              {newsletter.title}
            </h2>
            <p className="mt-3 max-w-md text-lg text-white/75">{newsletter.text}</p>
          </div>
          <NewsletterForm />
        </div>
      </Container>
    </section>
  );
}
