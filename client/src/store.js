const INPROGRESS_STATES = [
  "initializing",
  "queued",
  "backup_in_progress",
  "restore_in_progress",
];

const calculatePollDelay = (state) => {
  if (INPROGRESS_STATES.includes(state)) {
    return 2000;
  }

  return 5000;
};

export const useBackupStore = Statamic.$pinia.defineStore("itiden::backup", {
  state: () => ({
    status: "initializing",
    timeout: null,
  }),
  getters: {
    abilities: function (state) {
      return {
        backup: {
          isPossible: !INPROGRESS_STATES.includes(state.status),
          isPermitted: Statamic.$permissions.has("download backups"),
        },
        download: {
          isPossible: !INPROGRESS_STATES.includes(state.status),
          isPermitted: Statamic.$permissions.has("restore backups"),
        },
        restore: {
          isPossible: !INPROGRESS_STATES.includes(state.status),
          isPermitted: Statamic.$permissions.has("restore backups"),
        },
        destroy: {
          isPossible: !INPROGRESS_STATES.includes(state.status),
          isPermitted: Statamic.$permissions.has("delete backups"),
        },
      };
    },
  },
  actions: {
    setStatus(payload) {
      this.status = payload;
    },
    stopPolling() {
      clearTimeout(this.timeout);
      this.timeout = null;
    },
    startPolling() {
      const pollServerState = async () => {
        let state;
        try {
          const response =
            await window.Statamic.$app.config.globalProperties.$axios.get(
              cp_url("api/backups/state")
            );
          state = response.data.state;
          this.setStatus(response.data.state);
        } catch (error) {
          console.error("Error fetching server state:", error);
        } finally {
          const pollDelay = calculatePollDelay(state);

          this.timeout = setTimeout(pollServerState, pollDelay);
        }
      };

      pollServerState();
    },
  },
});
