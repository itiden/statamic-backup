const INPROGRESS_STATES = [
  "initializing",
  "backup_in_progress",
  "restore_in_progress",
];

export const store = {
  namespaced: true,
  state: () => ({
    status: "initializing",
    timeout: null,
  }),
  getters: {
    canBackup: (state) => {
      if (INPROGRESS_STATES.includes(state.status)) return false;

      return (
        Statamic.$store.state.statamic.config.user.super ??
        Statamic.$store.state.statamic.config.user.permissions.includes(
          "create backups"
        )
      );
    },
    canDownload: (state) => {
      if (INPROGRESS_STATES.includes(state.status)) return false;
      return (
        Statamic.$store.state.statamic.config.user.super ??
        Statamic.$store.state.statamic.config.user.permissions.includes(
          "download backups"
        )
      );
    },
    canRestore: (state) => {
      if (INPROGRESS_STATES.includes(state.status)) return false;

      return (
        Statamic.$store.state.statamic.config.user.super ??
        Statamic.$store.state.statamic.config.user.permissions.includes(
          "restore backups"
        )
      );
    },
    canDestroy: (state) => {
      if (INPROGRESS_STATES.includes(state.status)) return false;

      return (
        Statamic.$store.state.statamic.config.user.super ??
        Statamic.$store.state.statamic.config.user.permissions.includes(
          "delete backups"
        )
      );
    },
    abilities: function (state, getters) {
      return {
        backup: getters.canBackup,
        download: getters.canDownload,
        restore: getters.canRestore,
        destroy: getters.canDestroy,
      };
    },
  },
  mutations: {
    setStatus(state, payload) {
      state.status = payload;
      console.log("Server state:", state.status);
    },
    cancelPoll(state) {
      clearTimeout(state.timeout);
      state.commit("timeout", null);
    },
  },
  actions: {
    setStatus: ({ commit }, payload) => {
      commit("setStatus", payload);
    },
    stopPolling: ({ commit }) => {
      commit("cancelPoll");
    },
    pollEndpoint: ({ commit }) => {
      const pollServerState = async () => {
        try {
          const response = await Statamic.$axios.get(
            cp_url("api/backups/state")
          );
          commit("setStatus", response.data.state);
        } catch (error) {
          console.error("Error fetching server state:", error);
        } finally {
          commit("timeout", setTimeout(pollServerState, 5000));
        }
      };

      pollServerState();
    },
  },
};
