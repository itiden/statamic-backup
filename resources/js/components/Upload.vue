<template>
  <div>
    <div v-if="canCreateBackups && canUpload" ref="dropzone" class="btn mr-3">
      <svg-icon name="upload" class="h-4 w-4 mr-2 text-current" />
      <span>{{ __("Restore") }}</span>
    </div>

    <upload-status
      v-for="(file, index) in files"
      v-bind:key="file.file.uniqueIdentifier + index"
      :file="file.file"
      :status="file.status"
      :progress="file.progress"
    />
  </div>
</template>
<script>
import Resumable from "resumablejs";
import UploadStatus from "./UploadStatus.vue";
export default {
  components: {
    "upload-status": UploadStatus,
  },
  data() {
    return {
      files: [],
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
    // cancel an individual file
    cancelFile(file) {
      this.findFile(file).status = "canceled";
      file.cancel();
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
      });
      this.resumable.upload();
    });

    this.resumable.on("fileSuccess", (file, event) => {
      this.findFile(file).status = "success";
      this.$toast.success(JSON.parse(event).message);
      this.$emit("uploaded", JSON.parse(event).file);
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
