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
      show: false,
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
        .then((response) => {
          this.loading = false;
          this.show = false;

          this.$toast.success(__("Site is now backed up."));
          this.$root.$emit("onBackedup");
        })
        .catch(function (error) {
          console.log(error);
        });
    },
  },
};
</script>
