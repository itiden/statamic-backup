<template>
  <div>
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
    };
  },
  methods: {
    submit() {
      this.loading = true;
      this.confirming = false;

      this.$toast.success(__("Site is now being backed up."));
      this.$axios
        .post(cp_url("api/backups"), { comment: this.value })
        .then(({ data }) => {
          this.loading = false;

          this.$toast.success(__(data.message));
          this.$root.$emit("onBackedup");
        })
        .catch(function (error) {
          console.log(error);
        });
    },
  },
};
</script>
