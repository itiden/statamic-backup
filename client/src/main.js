import Backup from "./components/Backup.vue";
import { inertia } from '@statamic/cms/api'

Statamic.booting(() => {
  inertia.register('statamic-backup::page', Backup);
});
