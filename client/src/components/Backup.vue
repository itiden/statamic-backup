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

export default {
  components: {
    "backup-listing": Listing,
    "backup-actions": Actions,
  },

  data() {
    return {
      uploads: [],
      url: cp_url("/backups"),
    };
  },
};
</script>
