import type { Metadata } from "next";
import { Container } from "@/components/layout/Container";
import { PageHeader } from "@/components/layout/PageHeader";
import { OrderJourney } from "@/components/track/OrderJourney";
import { TrackHelp } from "@/components/track/TrackHelp";
import { TrackOrderForm } from "@/components/track/TrackOrderForm";

const description = "Track your Priniti Foods order. Order tracking will be available once online ordering and shipping are connected.";

export const metadata: Metadata = {
  title: { absolute: "Track Order | Priniti Foods" },
  description,
  alternates: { canonical: "/track-order" },
  robots: { index: false, follow: false },
  openGraph: { title: "Track Order | Priniti Foods", description, url: "/track-order" },
};

export default function TrackOrderPage() {
  return (
    <>
      <PageHeader eyebrow="Order status" title="Track Your Order" description="Enter your order details to check your order status." />
      <section className="bg-canvas py-6 lg:py-9">
        <Container className="flex flex-col gap-6 lg:gap-8">
          <div className="mx-auto w-full max-w-xl">
            <TrackOrderForm />
          </div>

          <div className="rounded-3xl bg-surface p-5 shadow-card ring-1 ring-line/70 sm:p-7">
            <h2 className="mb-5 text-center font-display text-xl font-extrabold tracking-tight sm:text-2xl">Your order journey</h2>
            <OrderJourney />
          </div>

          <TrackHelp />
        </Container>
      </section>
    </>
  );
}
