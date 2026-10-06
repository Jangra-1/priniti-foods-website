import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  reactStrictMode: true,
  // Hide the Next.js dev-tools badge (the round "N"); it never appears in production builds anyway.
  devIndicators: false,
  // Add remotePatterns here when product images move to a CDN / object storage.
  images: { remotePatterns: [] },
};

export default nextConfig;
