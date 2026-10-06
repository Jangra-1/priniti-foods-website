import { Container } from "@/components/layout/Container";
import { ButtonLink } from "@/components/ui/Button";

export default function NotFound() {
  return (
    <Container className="flex flex-col items-center gap-5 py-24 text-center">
      <h1 className="font-display text-4xl font-extrabold">Page not found</h1>
      <p className="max-w-md text-ink-soft">The page you are looking for does not exist, or the product is not available yet.</p>
      <div className="flex flex-wrap justify-center gap-3">
        <ButtonLink href="/shop">Browse all products</ButtonLink>
        <ButtonLink href="/" variant="secondary">
          Back to home
        </ButtonLink>
      </div>
    </Container>
  );
}
