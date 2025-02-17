const INPROGRESS_STATES = [
  "initializing",
  "queued",
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
    abilities: function (state) {
      return {
        backup: {
          isPossible: !INPROGRESS_STATES.includes(state.status),
          isPermitted:
            Statamic.$store.state.statamic.config.user.super ??
            Statamic.$store.state.statamic.config.user.permissions.includes(
              "download backups"
            ),
        },
        download: {
          isPossible: !INPROGRESS_STATES.includes(state.status),
          isPermitted:
            Statamic.$store.state.statamic.config.user.super ??
            Statamic.$store.state.statamic.config.user.permissions.includes(
              "restore backups"
            ),
        },
        restore: {
          isPossible: !INPROGRESS_STATES.includes(state.status),
          isPermitted:
            Statamic.$store.state.statamic.config.user.super ??
            Statamic.$store.state.statamic.config.user.permissions.includes(
              "restore backups"
            ),
        },
        destroy: {
          isPossible: !INPROGRESS_STATES.includes(state.status),
          isPermitted:
            Statamic.$store.state.statamic.config.user.super ??
            Statamic.$store.state.statamic.config.user.permissions.includes(
              "delete backups"
            ),
        },
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
