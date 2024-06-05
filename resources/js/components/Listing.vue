<template>
  <div>
    <div v-if="initializing" class="loading">
      <loading-graphic />
    </div>
    <data-list
      :visible-columns="columns"
      :columns="columns"
      :rows="items"
      v-show="items.length"
    >
      <div
        class="card overflow-hidden p-0 relative"
        slot-scope="{ filteredRows: rows }"
      >
        <data-list-bulk-actions
          class="rounded"
          :url="actionUrl"
          @started="actionStarted"
          @completed="actionCompleted"
        />

        <data-list-table>
          <template
            slot="actions"
            slot-scope="{ row: backup }"
            v-if="showActions"
          >
            <dropdown-list>
              <dropdown-item
                v-if="canDownload"
                :text="__('statamic-backup::backup.download')"
                :redirect="download_url(backup.timestamp)"
              />
              <span v-if="canRestore">
                <hr class="divider" />
                <dropdown-item
                  :text="__('statamic-backup::backup.restore')"
                  @click="initiateRestore(backup.timestamp, backup.name)"
                />
              </span>
              <span v-if="canDestroy">
                <hr class="divider" />
                <dropdown-item
                  :text="__('statamic-backup::backup.destroy')"
                  dangerous="true"
                  @click="initiateDestroy(backup.timestamp, backup.name)"
                />
              </span>
            </dropdown-list>
          </template>
        </data-list-table>
      </div>
    </data-list>

    <confirmation-modal
      v-if="confirmingRestore"
      :title="__('statamic-backup::backup.restore_title')"
      :bodyText="__(`statamic-backup::backup.restore_body`, { name: activeName })"
      :buttonText="__('statamic-backup::backup.restore')"
      @confirm="restore()"
      @cancel="confirmingRestore = false"
    />

    <confirmation-modal
      v-if="confirmingDestroy"
      :title="__('statamic-backup::backup.destroy_title')"
      :bodyText="__(`statamic-backup::backup.destroy_body`, { name: activeName })"
      :buttonText="__('statamic-backup::backup.destroy')"
      @confirm="destroy()"
      @cancel="confirmingDestroy = false"
    />
  </div>
</template>

<script>
import Listing from "../../../vendor/statamic/cms/resources/js/components/Listing.vue";

export default {
  mixins: [Listing],

  mounted() {
    this.$root.$on("onBackedup", this.request);
    this.$on("onDestroyed", this.request);
  },
  data() {
    return {
      requestUrl: cp_url("api/backups"),
      columns: this.initialColumns,
      confirmingRestore: false,
      confirmingDestroy: false,
      activeTimestamp: null,
      activeName: null,
      canDownload:
        this.$store.state.statamic.config.user.super ??
        this.$store.state.statamic.config.user.permissions.includes(
          "download backups"
        ),
      canRestore:
        this.$store.state.statamic.config.user.super ??
        this.$store.state.statamic.config.user.permissions.includes(
          "restore backups"
        ),
      canDestroy:
        this.$store.state.statamic.config.user.super ??
        this.$store.state.statamic.config.user.permissions.includes(
          "delete backups"
        ),
    };
  },
  computed: {
    showActions() {
      return this.canDownload || this.canRestore || this.canDestroy;
    },
  },
  methods: {
    download_url(timestamp) {
      return cp_url("api/backups/download/" + timestamp);
    },
    restore_url(timestamp) {
      return cp_url("api/backups/restore/" + timestamp);
    },
    destroy_url(timestamp) {
      return cp_url("api/backups/" + timestamp);
    },
    initiateDestroy(timestamp, name) {
      this.activeTimestamp = timestamp;
      this.activeName = name;
      this.confirmingDestroy = true;
    },
    initiateRestore(timestamp, name) {
      this.activeTimestamp = timestamp;
      this.activeName = name;
      this.confirmingRestore = true;
    },
    restore() {
      this.confirmingRestore = false;
      this.$toast.info(__('statamic-backup::backup.restore_started_name', {name:this.activeName}));
      this.$axios
        .post(this.restore_url(this.activeTimestamp))
        .then(({ data }) => {
          this.$toast.success(__(data.message));
          this.$emit("onRestored");
        })
        .catch((error) => {
          let message = __('statamic-backup::backup.restore_failed');

          if (error.response.data.message) {
            message = error.response.data.message;
          }
          this.$toast.error(__(message));
        })
        .finally(() => {
          this.activeName = null;
          this.activeTimestamp = null;
        });
    },
    destroy() {
      this.confirmingDestroy = false;
      this.$axios
        .delete(this.destroy_url(this.activeTimestamp))
        .then(({ data }) => {
          this.$toast.success(__(data.message));
          this.$emit("onDestroyed");
        })
        .catch((error) => {
          let message = "Something went wrong.";

          if (error.response.data.message) {
            message = error.response.data.message;
          }
          this.$toast.error(__(message));
        })
        .finally(() => {
          this.activeName = null;
          this.activeTimestamp = null;
        });
    },
  },
};
</script>
