<template>
  <div id="app">
    <router-view v-if="verified && !keyIssue.invalid"></router-view>
    <badaso-loading-page v-else title="Verifying Badaso" />
    <badaso-license-blocker />
    <badaso-prompt
      :active.sync="loader"
      buttons-hidden
      :title="title"
      type="confirm"
      class="badaso-loader"
      :color="color"
      :headerColor="headerColor"
    >
      <br />
      <vs-progress indeterminate :color="color">primary</vs-progress>
    </badaso-prompt>
  </div>
</template>

<script>
export default {
  name: "app",
  components: {},
  data: () => ({
    loader: false,
    title: "Loading",
    color: "primary",
    headerColor: null,
  }),
  methods: {
    openLoader(payload = null) {
      this.title = payload ? payload.title : "Loading";
      this.color = payload ? payload.color : "primary";
      this.headerColor = payload ? payload.headerColor : null;
      this.loader = true;
    },
    closeLoader() {
      this.loader = false;
    },
  },
  computed: {
    getSelectedLocale: {
      get() {
        return this.$store.getters["badaso/getSelectedLocale"];
      },
    },
    verified: {
      get() {
        return this.$store.getters["badaso/isVerified"];
      },
    },
    keyIssue: {
      get() {
        return this.$store.state.badaso.keyIssue;
      },
    },
  },
  mounted() {
    this.$i18n.locale = this.getSelectedLocale.key;
    this.$store.commit("badaso/FETCH_CONFIGURATION");
  },
  beforeMount() {
    this.$store.commit("badaso/VERIFY_BADASO");
  },
};
</script>
