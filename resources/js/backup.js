import Listing from "./components/Listing.vue";

Statamic.booting(() => {
  Statamic.$components.register("backup-listing", Listing);
});
