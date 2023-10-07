<template>
  <div class="flex">
    <upload />

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
</template>

<script>
import Upload from "./Upload.vue";
export default {
  components: {
    upload: Upload,
  },

  data() {
    return {
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
