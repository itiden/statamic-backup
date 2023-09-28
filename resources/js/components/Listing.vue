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
              <dropdown-item :text="__('Remove')" dangerous="true" />
            </dropdown-list>
          </template>
        </data-list-table>
      </div>
    </data-list>
  </div>
</template>

<script>
import Listing from "../../../vendor/statamic/cms/resources/js/components/Listing.vue";

export default {
  mixins: [Listing],

  data() {
    return {
      requestUrl: cp_url("api/backups"),
      columns: this.initialColumns,
    };
  },
  methods: {
    download_url: (timestamp) => {
      return cp_url("backups/download/" + timestamp);
    },
  },
};
</script>
