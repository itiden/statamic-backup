<template>
  <div>
    <div class="flex">
      <h1 class="mb-6">{{ __("statamic-backup::backup.title") }}</h1>
      <backup-actions @openBrowser="openBrowser" />
    </div>

    <backup-listing />
  </div>
</template>

<script>
import Listing from "./Listing.vue";
import Actions from "./Actions.vue";
import { store } from "../store";

export default {
  components: {
    "backup-listing": Listing,
    "backup-actions": Actions,
  },
  created() {
    if (!this.$store.hasModule('backup-provider')) {
      this.$store.registerModule('backup-provider', store);
      this.$store.dispatch('backup-provider/pollEndpoint');
    }
  },
  data() {
    return {
      serverState: "initializing",
      uploads: [],
      url: cp_url("/backups"),
    };
  },
  destroy() {
    this.$store.dispatch('backup-provider/stopPolling');
    this.$store.unregisterModule('backup-provider');
  },
};
</script>
