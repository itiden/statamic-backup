<template>
  <div v-if="canCreateBackups && canUpload" ref="dropzone" class="btn mr-3">
    <svg-icon name="upload" class="h-4 w-4 mr-2 text-current" />
    <span>{{ __("statamic-backup::backup.upload.label") }}</span>
  </div>
</template>
<script>
import Resumable from "resumablejs";

export default {
  props: {
    files: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      resumable: null,
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
    // finds the file in the local files array
    findFile(file) {
      return (
        this.files.find(
          (item) =>
            item.file.uniqueIdentifier === file.uniqueIdentifier &&
            item.status !== "canceled"
        ) ?? {}
      );
    },
  },
  mounted() {
    // init resumablejs on mount
    this.resumable = new Resumable({
      target: cp_url("backups/chunky"),
      testTarget: cp_url("backups/chunky/test"),
      headers: {
        Accept: "application/json",
        "X-CSRF-TOKEN":
          window.document.querySelector("input[name=_token]").value,
      },
      maxChunkRetries: 1,
      maxFiles: 1,
      testChunks: false,
    });

    // Resumable.js isn't supported, fall back on a different method
    if (!this.resumable.support)
      return alert(
        "Your browser doesn't support chunked uploads. Get a better browser."
      );

    this.resumable.assignBrowse(this.$refs.dropzone);
    this.resumable.assignDrop(this.$refs.dropzone);

    // set up event listeners to feed into vues reactivity
    this.resumable.on("fileAdded", (file, event) => {
      file.hasUploaded = false;
      this.files.push({
        file,
        status: "uploading",
        progress: 0,
        path: null,
      });
      this.resumable.upload();
    });

    this.resumable.on("fileSuccess", (file, event) => {
      const data = JSON.parse(event);
      this.findFile(file).status = "success";
      this.findFile(file).path = data.file;
      this.$toast.success(data.message);
      this.$emit("uploaded", data.file);
    });

    this.resumable.on("fileError", (file, event) => {
      this.findFile(file).status = "error";
      this.$toast.error(JSON.parse(event).message);
    });

    this.resumable.on("fileRetry", (file, event) => {
      this.findFile(file).status = "retrying";
    });

    this.resumable.on("fileProgress", (file) => {
      // console.log('fileProgress', progress)
      const localFile = this.findFile(file);
      // if we are doing multiple chunks we may get a lower progress number if one chunk response comes back early
      const progress = file.progress();
      if (progress > localFile.progress) localFile.progress = progress;
    });
  },
};
</script>
