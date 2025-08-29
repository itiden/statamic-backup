import Resumable from "resumablejs";
import { ref, watch } from "vue";

/**
 * @param {{
 *  chunkSize: number,
 *  dropZone:  import("vue").Ref<HTMLElement>,
 *  browseTarget: import("vue").Ref<HTMLElement>,
 *  onFileUploaded: (file: File) => void
 * }}
 */
export const useResumable = ({
  chunkSize,
  dropZone,
  browseTarget,
  onFileUploaded,
}) => {
  const files = ref([]);

  const findFile = (file) =>
    files.value.find(
      (item) =>
        item.file.uniqueIdentifier === file.uniqueIdentifier &&
        item.status !== "canceled"
    ) ?? {};

  const resumable = new Resumable({
    target: cp_url("backups/chunky"),
    testTarget: cp_url("backups/chunky/test"),
    headers: {
      Accept: "application/json",
      "X-CSRF-TOKEN": window?.Statamic.$config.get("csrfToken"),
    },
    fileType: ["zip"],
    testChunks: true,
    chunkSize: chunkSize, // 2MB
    forceChunkSize: true,
    maxChunkRetries: 1,
    maxFiles: 1,
  });

  if (!resumable.support)
    return alert(
      "Your browser doesn't support chunked uploads. Get a better browser."
    );

  watch(dropZone, (target) => {
    if (target) {
      resumable.assignDrop(target.$el);
    }
  });
  watch(browseTarget, (target) => {
    if (target) {
      resumable.assignBrowse(target.$el);
    }
  });

  // set up event listeners to feed into vues reactivity
  resumable.on("fileAdded", (file, event) => {
    file.hasUploaded = false;
    files.value = [
      ...files.value,
      {
        file,
        status: "uploading",
        progress: 0,
        path: null,
      },
    ];
    window.Statamic.$progress.start(file.uniqueIdentifier);
    resumable.upload();
  });

  resumable.on("fileSuccess", (file, event) => {
    const data = JSON.parse(event);

    window.Statamic.$toast.success(data.message);

    window.Statamic.$progress.complete(file.uniqueIdentifier);
    onFileUploaded?.(findFile(file));

    files.value = files.value.filter(
      (item) => item.file.uniqueIdentifier !== file.uniqueIdentifier
    );
  });

  resumable.on("fileError", (file, event) => {
    findFile(file).status = "error";
  });

  resumable.on("fileRetry", (file, event) => {
    findFile(file).status = "retrying";
  });

  resumable.on("fileProgress", (file) => {
    const localFile = findFile(file);
    // if we are doing multiple chunks we may get a lower progress number if one chunk response comes back early
    const progress = file.progress();
    if (progress > localFile.progress) localFile.progress = progress;
  });

  return { files };
};
