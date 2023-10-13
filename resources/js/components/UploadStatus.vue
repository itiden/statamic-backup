<template>
  <div
    class="flex items-center my-4 w-full justify-end"
    :class="{ 'text-red-500': status == 'error' }"
  >
    <div class="mx-2 flex items-center">
      <svg-icon
        name="micro/sharp-trash"
        class="h-4 w-4"
        v-if="status === 'canceled'"
      />
      <svg-icon
        name="micro/warning"
        class="text-red-500 h-4 w-4"
        v-else-if="status === 'error'"
      />
      <loading-graphic
        v-else-if="status === 'uploading'"
        :inline="true"
        text=""
      />
    </div>

    <div class="filename">{{ basename }}</div>

    <div
      v-if="status !== 'error' && status !== 'canceled' && status !== 'success'"
      class="bg-white flex-1 h-4 mx-2 rounded"
    >
      <div class="bg-blue h-full rounded" :style="{ width: percent + '%' }" />
    </div>

    <div class="px-2">
      <p v-if="status === 'canceled'">Canceled</p>

      <div v-if="status === 'error'">
        {{ error }}
        <button
          @click.prevent="clear"
          class="flex items-center text-gray-700 hover:text-gray-800"
        >
          <svg-icon name="micro/circle-with-cross" class="h-4 w-4" />
        </button>
      </div>

      <div v-if="status === 'uploading'">
        <button
          @click.prevent="cancel"
          class="flex items-center text-gray-700 hover:text-gray-800"
        >
          <svg-icon name="micro/circle-with-cross" class="h-4 w-4" />
        </button>
      </div>
      <button
        v-if="status === 'success'"
        @click.prevent="restore"
        class="btn-primary"
      >
        <svg-icon name="folder-home" class="h-4 w-4 mr-2 text-current" />
        <span>{{ __("Restore") }}</span>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  props: ["basename", "percent", "status", "file"],

  methods: {
    clear() {
      this.$emit("clear");
    },
    cancel() {
      this.file.file.cancel();
      this.file.status = "canceled";
      this.$emit("cancel", this.file);
    },
    pause() {
      this.$emit("pause", this.file);
    },
    restore() {
      this.$emit("restore", this.file);
    },
  },
};
</script>
