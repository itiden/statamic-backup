<template>
  <div class="flex flex-col items-end w-full">
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

    <ul class="mt-3 mb-3 w-full">
      <upload-status
        v-for="(file, index) in files"
        v-bind:key="file.file.uniqueIdentifier + index"
        :basename="file.file.fileName"
        :status="file.status"
        :percent="file.progress * 100"
        :file="file"
        @restore="restore(file)"
      />
    </ul>
  </div>
</template>

<script>
import UploadButton from "./Upload.vue";
import UploadStatus from "./UploadStatus.vue";

export default {
  components: {
    upload: UploadButton,
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
    restore(file) {
      this.loading = true;
      this.confirming = false;
      file.status = "restoring";

      this.$toast.info(__("Starting restore..."));
      this.$axios
        .post(cp_url("api/backups/restore-from-path"), {
          path: file.path,
          destroyAfterRestore: true,
        })
        .then(({ data }) => {
          this.$toast.success(__(data.message));
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
          file.status = "restored";
        });
    },
  },
};
</script>
