<template>
  <div class="flex flex-col items-end w-full">
    <div class="flex justify-end">
      <upload :files="files" />

      <button
        v-if="canCreateBackups.isPermitted"
        :disabled="loading || !canCreateBackups.isPossible"
        class="btn-primary"
        :class="{ 'btn-disabled': loading }"
        @click="backup()"
      >
        <svg-icon name="upload-cloud" class="h-4 w-4 mr-2 text-current" />
        <span>{{ __("statamic-backup::backup.create") }}</span>
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
    };
  },
  computed: {
    status() {
      return this.$store.state['backup-provider'].status;
    },
    canCreateBackups() {
      return this.$store.getters['backup-provider/abilities'].backup;
    },
    canUpload() {
      return this.$store.getters['backup-provider/abilities'].restore;
    },
  },
  methods: {
    backup() {
      this.loading = true;
      this.confirming = false;

      this.$store.dispatch('backup-provider/setStatus', 'backup_in_progress');
      this.$axios
        .post(cp_url("api/backups"), { comment: this.value })
        .then(({ data }) => {
          this.$toast.info(__(data.message));
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

      this.$store.dispatch('backup-provider/setStatus','restore_in_progress');

      this.$axios
        .post(cp_url("api/backups/restore-from-path"), {
          path: file.path,
        })
        .then(({ data }) => {
          this.$toast.info(__(data.message));
        })
        .catch((error) => {
          let message = __("statamic-backup::backup.restore.failed");

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
