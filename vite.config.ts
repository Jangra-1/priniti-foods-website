import { fileURLToPath } from "node:url";
import react from "@vitejs/plugin-react";
import { defineConfig } from "vite";

/**
 * Builds the theme's interactive islands (React) into theme/priniti/assets/dist/js.
 * PHP renders all markup; these bundles only hydrate the interactive overlays.
 */
export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: { "@theme": fileURLToPath(new URL("./theme/priniti/assets/src/js", import.meta.url)) },
  },
  define: { "process.env.NODE_ENV": JSON.stringify("production") },
  build: {
    outDir: "theme/priniti/assets/dist/js",
    emptyOutDir: true,
    manifest: true,
    sourcemap: false,
    target: "es2022",
    rollupOptions: {
      input: { app: fileURLToPath(new URL("./theme/priniti/assets/src/js/app.tsx", import.meta.url)) },
      output: { entryFileNames: "[name].[hash].js", chunkFileNames: "[name].[hash].js", assetFileNames: "[name].[hash][extname]" },
    },
  },
});
