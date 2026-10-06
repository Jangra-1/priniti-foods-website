# reference/

`nextjs/` is the Priniti Foods storefront project exactly as supplied (Next.js 16, React 19, Tailwind CSS v4). It is the **source of truth** for the website's design and UX and for the catalog data (`nextjs/data`).

- Do not edit it to make the WordPress theme easier to build. If the design changes, update it here first, then port the change to `theme/priniti`.
- It is never deployed. Run it locally to compare against the theme:
  `cd reference/nextjs && npm install && NEXT_PUBLIC_TEST_PRICES=off npx next build && npx next start`
