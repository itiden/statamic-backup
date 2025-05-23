<template>
  <div v-if="canCreateBackups.isPermitted && canUpload.isPermitted && canUpload.isPossible" ref="dropzone" class="btn mr-3">
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
    };
  },
  computed: {
    canCreateBackups() {
      return this.$store.getters['backup-provider/abilities'].backup;
    },
    canUpload() {
      return this.$store.getters['backup-provider/abilities'].restore;
    },
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
          document.querySelector("input[name=_token]").value,
      },
      fileType: ["zip"],
      testChunks: true,
      chunkSize: window.backup.chunkSize || 2 * 1024 * 1024, // 2MB
      forceChunkSize: true,
      maxChunkRetries: 1,
      maxFiles: 1,
    });

    // Resumable.js isn't supported, fall back on a different method
    if (!this.resumable.support) return alert("Your browser doesn't support chunked uploads. Get a better browser.");


    this.$watch(
      (state) => {
        return state.canCreateBackups.isPermitted && state.canUpload.isPermitted && state.canUpload.isPossible
      },
      (newValue) => {
        if (newValue) {
          if (this.$refs.dropzone) {
            this.resumable.assignBrowse(this.$refs.dropzone);
            this.resumable.assignDrop(this.$refs.dropzone);
          }
        }
      }
    );

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
      const data = JSON.parse(event);;
      this.$toast.success(data.message);
      this.$root.$emit("uploaded", file);
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
