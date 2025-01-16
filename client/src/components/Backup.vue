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
  mounted: function () {
    const pollServerState = async () => {
      try {
        const response = await this.getServerState();
        this.serverState = response.data.state;
        console.log("Server state:", this.serverState);
      } catch (error) {
        console.error("Error fetching server state:", error);
      } finally {
        this.pollTimeout = setTimeout(pollServerState, 5000);
      }
    };

    pollServerState();
  },
  computed: {
    canDownload: function() {
      if (['initializing','backup_in_progress', 'restore_in_progress'].includes(this.serverState)) return false;
      return  this.$store.state.statamic.config.user.super ??
      this.$store.state.statamic.config.user.permissions.includes(
        "download backups"
      )
    },
    canRestore: function() {
      if (['initializing','backup_in_progress', 'restore_in_progress'].includes(this.serverState)) return false;

      return this.$store.state.statamic.config.user.super ??
      this.$store.state.statamic.config.user.permissions.includes(
        "restore backups");
    },
    canDestroy: function() {
      if (['initializing','backup_in_progress', 'restore_in_progress'].includes(this.serverState)) return false;

      return this.$store.state.statamic.config.user.super ??
      this.$store.state.statamic.config.user.permissions.includes(
        "delete backups"
      );
    },
    abilities: function() {
      return {
        download: this.canDownload,
        restore: this.canRestore,
        destroy: this.canDestroy,
      };
    },
  },
  methods: {
    getServerState: async function () {
      return await this.$axios.get(cp_url("api/backups/state"));
    },
  },
  data() {
    return {
      pollTimeout: null,
      serverState: "initializing",
      uploads: [],
      url: cp_url("/backups"),
    };
  },
  destroy() {
    clearTimeout(this.pollTimeout);
  },
};
</script>
