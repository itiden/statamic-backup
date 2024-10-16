import { defineConfig } from "vitepress";

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "Statamic Backup",
  description: 'Documentation for the statamic addon "Statamic backup"',
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    nav: [{ text: "Start", link: "/" }],

    sidebar: [
      {
        text: "Introduction",
        items: [{ text: "Getting started", link: "/getting-started.md" }],
      },
      {
        text: "Configuration",
        items: [
          { text: "Options", link: "/configuration.md" },
          { text: "Scheduling", link: "/scheduling.md" },
        ],
      },
      {
        text: "Extending",
        items: [
          { text: "Pipeline", link: "/pipeline.md" },
          { text: "Notifications", link: "/notifications.md" },
        ],
      },
    ],

    search: {
      provider: "local",
    },

    socialLinks: [
      { icon: "github", link: "https://github.com/itiden/statamic-backup" },
    ],
  },
});
