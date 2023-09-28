<template>
  <div>
    <div class="flex justify-between">
      <h1 class="mb-6">Backups</h1>
      <backup-actions />
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
};
</script>
