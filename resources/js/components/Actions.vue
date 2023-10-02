<template>
  <div v-if="canCreateBackups">
    <button
      :disabled="loading"
      class="btn-primary"
      :class="{ 'btn-disabled': loading }"
      @click="submit()"
    >
      Backup
    </button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      confirming: false,
      loading: false,
      canCreateBackups:
        this.$store.state.statamic.config.user.super ??
        this.$store.state.statamic.config.user.permissions.includes(
          "create backups"
        ),
    };
  },
  methods: {
    submit() {
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
