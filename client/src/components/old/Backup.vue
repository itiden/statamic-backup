<template>
  <div>
    <div class="flex">
      <div class="flex flex-col mb-4">
        <h1>{{ __("statamic-backup::backup.title") }}</h1>
        <p v-if="status !== 'idle'" class="text-sm text-gray-700 whitespace-nowrap">
          {{ __(`statamic-backup::backup.state.${status}`) }}
        </p>
      </div>
      <backup-actions @openBrowser="openBrowser" />
    </div>

    <backup-listing />
  </div>
</template>

<script>
import Listing from "./Listing.vue";
import Actions from "./Actions.vue";
import { store } from "../../store";

export default {
  components: {
    "backup-listing": Listing,
    "backup-actions": Actions,
  },
  props: {
    chunkSize: {
      type: Number,
      default: 2 * 1024 * 1024, // 2MB
    },
  },
  created() {
    // console.log(this.chunkSize)

    window.backup = {
      chunkSize: this.chunkSize
    };

    if (!this.$store.hasModule('backup-provider')) {
      this.$store.registerModule('backup-provider', store);
      this.$store.dispatch('backup-provider/pollEndpoint');
    }
  },
  computed: {
    status() {
      return this.$store.state['backup-provider'].status;
    },
  },
  destroy() {
    this.$store.dispatch('backup-provider/stopPolling');
    this.$store.unregisterModule('backup-provider');
  },
};
</script>
