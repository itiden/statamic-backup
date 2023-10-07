<template>
  <div class="flex flex-col">
    <div class="flex justify-end">
      <upload :files="files" />

      <button
        v-if="canCreateBackups"
        :disabled="loading"
        class="btn-primary"
        :class="{ 'btn-disabled': loading }"
        @click="backup()"
      >
        <svg-icon name="upload-cloud" class="h-4 w-4 mr-2 text-current" />
        <span>{{ __("Backup") }}</span>
      </button>
    </div>
    <ul class="flex flex-col items-end gap-3 mt-3 mb-3">
      <upload-status
        v-for="(file, index) in files"
        v-bind:key="file.file.uniqueIdentifier + index"
        :path="file.path"
        :file="file.file"
        :status="file.status"
        :progress="file.progress"
      />
    </ul>
  </div>
</template>

<script>
import Upload from "./Upload.vue";
import UploadStatus from "./UploadStatus.vue";

export default {
  components: {
    upload: Upload,
    "upload-status": UploadStatus,
  },

  data() {
    return {
      files: [],
      confirming: false,
      loading: false,
      canCreateBackups:
        this.$store.state.statamic.config.user.super ??
        this.$store.state.statamic.config.user.permissions.includes(
          "create backups"
        ),
      canUpload:
        this.$store.state.statamic.config.user.super ??
        this.$store.state.statamic.config.user.permissions.includes(
          "restore backups"
        ),
    };
  },
  methods: {
    backup() {
      this.loading = true;
      this.confirming = false;

      this.$toast.info(__("Starting backup..."));
      this.$axios
        .post(cp_url("api/backups"), { comment: this.value })
        .then(({ data }) => {
          this.$toast.success(__(data.message));
          this.$root.$emit("onBackedup");
        })
        .catch((error) => {
          let message = "Something went wrong.";

          if (error.response.data.message) {
            message = error.response.data.message;
          }
          this.$toast.error(__(message));
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
};
</script>
