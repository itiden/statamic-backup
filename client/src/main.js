import Backup from "./components/Backup.vue";

Statamic.booting(() => {
  Statamic.$components.register("itiden-backup", Backup);
});
