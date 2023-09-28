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
          <template slot="actions" slot-scope="{ row: backup }">
            <dropdown-list>
              <dropdown-item
                :text="__('Download')"
                :redirect="download_url(backup.timestamp)"
              />
              <dropdown-item
                :text="__('Restore')"
                @click="initiateRestore(backup.timestamp, backup.name)"
              />
              <dropdown-item :text="__('Remove')" dangerous="true" />
            </dropdown-list>
          </template>
        </data-list-table>
      </div>
    </data-list>
    <confirmation-modal
      v-if="confirming"
      title="Restore Site"
      :bodyText="`Are you sure you want to restore your site to the state it was ${activeName} ?`"
      buttonText="Restore"
      @confirm="restore()"
      @cancel="confirming = false"
    />
  </div>
</template>

<script>
import Listing from "../../../vendor/statamic/cms/resources/js/components/Listing.vue";

export default {
  mixins: [Listing],

  mounted() {
    this.$root.$on("onBackedup", () => {
      this.request();
    });
  },
  data() {
    return {
      requestUrl: cp_url("api/backups"),
      columns: this.initialColumns,
      confirming: false,
      activeTimestamp: null,
      activeName: null,
    };
  },
  methods: {
    download_url(timestamp) {
      return cp_url("api/backups/download/" + timestamp);
    },
    restore_url(timestamp) {
      return cp_url("api/backups/restore/" + timestamp);
    },
    initiateRestore(timestamp, name) {
      this.activeTimestamp = timestamp;
      this.activeName = name;
      this.confirming = true;
    },
    restore() {
      this.confirming = false;
      this.$axios
        .post(this.restore_url(this.activeTimestamp))
        .then(() => {
          this.$toast.success(__("Site is now restored."));
          this.$emit("onRestored");
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
