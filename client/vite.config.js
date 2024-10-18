import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue2";

export default defineConfig({
  build: {
    emptyOutDir: true,
  },
  plugins: [
    laravel({
      input: ["src/main.js"],
      publicDirectory: "./../resources/dist",
    }),
    vue(),
  ],
});
