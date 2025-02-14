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
          <template slot="cell-name" slot-scope="{ row: backup, value }">
            <div class="flex items-center">
              <svg-icon name="alert" class="h-4 w-4 mr-2 text-orange" v-if="backup.metadata.skipped_pipes.length" v-tooltip="{ content: backup.metadata.skipped_pipes.map(x => `${x.pipe}: ${x.reason}`).join('<br/>'), html:true}"/>
              {{ value }}
            </div>
          </template>

          <template
            slot="actions"
            slot-scope="{ row: backup }"
            v-if="showActions"
          >
            <dropdown-list>
              <dropdown-item
                v-if="canDownload"
                :text="__('statamic-backup::backup.download.label')"
                :redirect="download_url(backup.timestamp)"
              />
              <span v-if="canRestore">
                <hr class="divider" />
                <dropdown-item
                  :text="__('statamic-backup::backup.restore.label')"
                  @click="initiateRestore(backup.timestamp, backup.name)"
                />
              </span>
              <span v-if="canDestroy">
                <hr class="divider" />
                <dropdown-item
                  :text="__('statamic-backup::backup.destroy.label')"
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
      :title="__('statamic-backup::backup.restore.confirm_title')"
      :bodyText="__(`statamic-backup::backup.restore.confirm_body`, { name: activeName })"
      :buttonText="__('statamic-backup::backup.restore.label')"
      @confirm="restore()"
      @cancel="confirmingRestore = false"
    />

    <confirmation-modal
      v-if="confirmingDestroy"
      :title="__('statamic-backup::backup.destroy.confirm_title')"
      :bodyText="__(`statamic-backup::backup.destroy.confirm_body`, { name: activeName })"
      :buttonText="__('statamic-backup::backup.destroy.label')"
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
    this.$on("onDestroyed", this.request);
  },
  watch: {
    status(newStatus, oldStatus) {
      console.log({newStatus, oldStatus});
      if (newStatus === oldStatus || oldStatus === 'initializing') return;

      const completed = ["backup_completed", "restore_completed"];

      if (completed.includes(newStatus)) {
        this.request();
        this.$toast.success(__(`statamic-backup::backup.success`));
      }
    }
  },
  data() {
    return {
      requestUrl: cp_url("api/backups"),
      columns: this.initialColumns,
      confirmingRestore: false,
      confirmingDestroy: false,
      activeTimestamp: null,
      activeName: null,
    };
  },
  computed: {
    status() {
      return this.$store.state['backup-provider'].status;
    },
    canDownload() {
      return this.$store.getters['backup-provider/abilities'].download;
    },
    canRestore() {
      return this.$store.getters['backup-provider/abilities'].restore;
    },
    canDestroy() {
      return this.$store.getters['backup-provider/abilities'].destroy;
    },
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

      if (!this.canRestore) return console.warn("Cannot restore backups.");

      this.$toast.info(__('statamic-backup::backup.restore.started_name', {name:this.activeName}));
      this.$store.dispatch('backup-provider/setStatus', 'restore_in_progress');
      this.$axios
        .post(this.restore_url(this.activeTimestamp))
        .then(({ data }) => {
          this.$toast.success(__(data.message));
          this.$emit("onRestored");
        })
        .catch((error) => {
          let message = __('statamic-backup::backup.restore.failed');

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
      if (!this.canDestroy) return console.warn("Cannot destroy backups.");

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
