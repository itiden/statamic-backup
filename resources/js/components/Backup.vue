<template>
  <uploader
    :enabled="true"
    @updated="uploadsUpdated"
    :url="url"
    @upload-complete="uploadCompleted"
    @error="uploadError"
  >
    <div slot-scope="{ dragging }" class="min-h-screen">
      <div class="drag-notification" v-show="dragging">
        <svg-icon name="upload" class="h-12 w-12 m-4" />
        <span>{{ __("Drop File to Upload") }}</span>
      </div>

      <div class="flex justify-between">
        <h1 class="mb-6">Backups</h1>
        <backup-actions />
      </div>

      <uploads v-if="uploads.length" :uploads="uploads" class="-mt-px" />

      <backup-listing />
    </div>
  </uploader>
</template>
<script>
import Listing from "./Listing.vue";
import Actions from "./Actions.vue";
import Uploader from "../../../vendor/statamic/cms/resources/js/components/assets/Uploader.vue";
import Uploads from "../../../vendor/statamic/cms/resources/js/components/assets/Uploads.vue";

export default {
  components: {
    "backup-listing": Listing,
    "backup-actions": Actions,
    Uploader,
    Uploads,
  },
  data() {
    return {
      uploads: [],
      url: cp_url("/backups"),
    };
  },
  methods: {
    uploadsUpdated(uploads) {
      this.uploads = uploads;
    },
    uploadCompleted(backup) {
      this.$toast.success(backup.filename + " uploaded successfully.");
    },
    uploadError(error) {
      console.log(error);
      this.$toast.error(error);
    },
  },
};
</script>
