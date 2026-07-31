import { defineConfig } from "vitepress";
import fs from 'node:fs'
import path from "node:path";

function capitalizeFirstLetter([ first='', ...rest ]) {
  return [ first.toUpperCase(), ...rest ].join('');
}

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "Statamic Backup",
  description: 'Documentation for the statamic addon "Statamic backup"',
  base: "/statamic-backup",
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    nav: [{ text: "Start", link: "/" }],

    sidebar: [
      {
        text: "Introduction",
        items: [
          { text: "Getting started", link: "/getting-started.md" },
          { text: "Commands", link: "/commands.md" },
          { text: "Events", link: "/events.md" },
        ],
      },
      {
        text: "Configuration",
        items: [
          { text: "Options", link: "/configuration.md" },
          { text: "Scheduling", link: "/scheduling.md" },
        ],
      },
      {
        text: "Recipies",
        items: fs.readdirSync(path.normalize(import.meta.dirname + '/../recipies'))
          .map(file => ({
            text: capitalizeFirstLetter(path.parse(file).name),
            link: `/recipies/${file}`
          }))
      },
      {
        text: "Extending",
        items: [
          { text: "Pipeline", link: "/pipeline.md" },
          { text: "Metadata", link: "/metadata.md" },
        ],
      },
      {
        text: "Advanced",
        items: [{ text: "Naming backups", link: "/naming-backups.md" }],
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
