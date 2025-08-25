import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import statamic from "@statamic/cms/vite-plugin";

export default defineConfig({
  build: {
    emptyOutDir: true,
  },
  plugins: [
    statamic(),
    laravel({
      refresh: true,
      input: ["src/main.js"],
      publicDirectory: "./../resources/dist",
    }),
  ],
});
