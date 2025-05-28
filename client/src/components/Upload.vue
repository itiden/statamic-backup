<template>
  <div>
    <div class="drag-notification" :class="{ 'hidden': !isDragging }" ref="dropzone">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-12 w-12 m-4">
        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M.752 2.251a1.5 1.5 0 0 1 1.5-1.5m0 22.5a1.5 1.5 0 0 1-1.5-1.5m22.5 0a1.5 1.5 0 0 1-1.5 1.5m0-22.5a1.5 1.5 0 0 1 1.5 1.5m0 15.75v-1.5m0-3.75v-1.5m0-3.75v-1.5m-22.5 12v-1.5m0-3.75v-1.5m0-3.75v-1.5m5.25-5.25h1.5m3.75 0h1.5m3.75 0h1.5m-12 22.5h1.5m3.75 0h1.5m3.75 0h1.5m-6-5.25v-12m4.5 4.5-4.5-4.5-4.5 4.5"></path>
      </svg>
      <span>Drop File to Upload</span>
    </div>
    <div v-if="canCreateBackups.isPermitted && canUpload.isPermitted && canUpload.isPossible" ref="browse" class="btn mr-3">
      <svg-icon name="upload" class="h-4 w-4 mr-2 text-current" />
      <span>{{ __("statamic-backup::backup.upload.label") }}</span>
    </div>
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
      isDragging: false,
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

    const backupElement = document.getElementById("statamic-backup");

    this.$watch(
      (state) => {
        return state.canCreateBackups.isPermitted && state.canUpload.isPermitted && state.canUpload.isPossible
      },
      (newValue) => {
        if (newValue) {
          if (this.$refs.browse) {
            this.resumable.assignBrowse(this.$refs.browse);
            this.resumable.assignDrop(backupElement);
          }
        }
      }
    );

    this.resumable.handleDropEvent = console.log;

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

    backupElement.addEventListener("dragover", (event) => {
      event.preventDefault();
      this.isDragging = true;
    });
    backupElement.addEventListener("dragleave", (event) => {
      event.preventDefault();
      this.isDragging = false;
    });
    backupElement.addEventListener("drop", (event) => {
      event.preventDefault();
      this.isDragging = false;
    });
  },
};
</script>
