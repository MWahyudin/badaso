<template>
  <div id="parentx">
    <vs-sidebar
      default-index="1"
      :parent="parent"
      :hiddenBackground="doNotClose"
      color="primary"
      class="sidebarx"
      spacer
      v-model="isSidebarActive"
      :click-not-close="doNotClose"
      :reduce="reduceSidebar"
    >
      <div class="header-sidebar text-center" slot="header">
        <vs-avatar size="70px" :src="`${$api.badasoFile.view(user.avatar)}`" />
        <!--
        <h4>
          {{user.name}}
          <br />
          <small>
            {{user.email}}
          </small>
        </h4>
        -->
        <badaso-sidebar-group
          :title="user.name"
          :subTitle="user.email"
          icon="person_pin"
        >
          <badaso-sidebar-item
            v-if="user.id"
            icon="person_outline"
            :to="{
              name: 'UserProfile',
            }"
          >
            Profile
          </badaso-sidebar-item>
          <badaso-sidebar-item icon="logout" @click="logout()">
            Logout
          </badaso-sidebar-item>
        </badaso-sidebar-group>
        <vs-select
          v-model="selectedLang"
          width="100%"
          style="padding: 10px;"
          v-if="view == $constants.MOBILE"
        >
          <vs-select-item
            :key="index"
            :value="item.key ? item.key : item"
            :text="item.label ? item.label : item.key ? item.key : item"
            v-for="(item, index) in getLocale"
          />
        </vs-select>
      </div>
      <badaso-sidebar-item icon="dashboard" :to="`/${prefix}/home`">
        <span class="hide-in-minisidebar">{{ $t("sidebar.dashboard") }}</span>
      </badaso-sidebar-item>
      <badaso-sidebar-group
        :title="mainMenu.menu.displayName"
        open
        :icon="mainMenu.menu.icon"
        v-if="mainMenu.menuItems && mainMenu.menuItems.length > 0"
      >
        <template v-for="(menu, index) in mainMenu.menuItems">
          <badaso-sidebar-group
            v-if="menu.children && menu.children.length > 0"
            :title="menu.title"
            open
            :icon="menu.iconClass"
          >
            <template v-for="(childMenu, indexChildMenu) in menu.children">
              <badaso-sidebar-item
                v-if="$helper.isValidHttpUrl(childMenu.url)"
                :icon="childMenu.iconClass ? childMenu.iconClass : 'remove'"
                :href="menu.url"
                :key="`menu-${index}-${indexChildMenu}`"
                :index="`${index}.${indexChildMenu}`"
                :style="`color: ${childMenu.color}`"
                :target="menu.target"
              >
                <span class="hide-in-minisidebar">{{ childMenu.title }}</span>
              </badaso-sidebar-item>
              <badaso-sidebar-item
                v-else
                :icon="childMenu.iconClass ? childMenu.iconClass : 'remove'"
                :to="childMenu.url"
                :key="`menu-${index}-${indexChildMenu}`"
                :index="`${index}.${indexChildMenu}`"
                :style="`color: ${childMenu.color}`"
                :target="menu.target"
              >
                <span class="hide-in-minisidebar">{{ childMenu.title }}</span>
              </badaso-sidebar-item>
            </template>
          </badaso-sidebar-group>
          <div v-else>
            <badaso-sidebar-item
              v-if="$helper.isValidHttpUrl(menu.url)"
              :icon="menu.iconClass ? menu.iconClass : 'remove'"
              :href="menu.url"
              :key="`menu-${index}`"
              :index="index"
              :style="`color: ${menu.color}`"
              :target="menu.target"
            >
              <span class="hide-in-minisidebar">{{ menu.title }}</span>
            </badaso-sidebar-item>
            <badaso-sidebar-item
              v-else
              :icon="menu.iconClass ? menu.iconClass : 'remove'"
              :to="menu.url"
              :key="`menu-${index}`"
              :index="index"
              :style="`color: ${menu.color}`"
              :target="menu.target"
            >
              <span class="hide-in-minisidebar">{{ menu.title }}</span>
            </badaso-sidebar-item>
          </div>
        </template>
      </badaso-sidebar-group>

      <badaso-sidebar-group
        :title="configurationMenu.menu.displayName"
        open
        :icon="configurationMenu.menu.icon"
        v-if="
          configurationMenu.menuItems && configurationMenu.menuItems.length > 0
        "
      >
        <template v-for="(menu, index) in configurationMenu.menuItems">
          <badaso-sidebar-group
            v-if="menu.children && menu.children.length > 0"
            :title="menu.title"
            open
            :icon="menu.iconClass"
          >
            <template v-for="(childMenu, indexChildMenu) in menu.children">
              <badaso-sidebar-item
                v-if="$helper.isValidHttpUrl(childMenu.url)"
                :icon="childMenu.iconClass ? childMenu.iconClass : 'remove'"
                :href="menu.url"
                :key="`menu-${index}-${indexChildMenu}`"
                :index="`${index}.${indexChildMenu}`"
                :style="`color: ${childMenu.color}`"
                :target="menu.target"
              >
                <span class="hide-in-minisidebar">{{ childMenu.title }}</span>
              </badaso-sidebar-item>
              <badaso-sidebar-item
                v-else
                :icon="childMenu.iconClass ? childMenu.iconClass : 'remove'"
                :to="childMenu.url"
                :key="`menu-${index}-${indexChildMenu}`"
                :index="`${index}.${indexChildMenu}`"
                :style="`color: ${childMenu.color}`"
                :target="menu.target"
              >
                <span class="hide-in-minisidebar">{{ childMenu.title }}</span>
              </badaso-sidebar-item>
            </template>
          </badaso-sidebar-group>
          <div v-else>
            <badaso-sidebar-item
              v-if="$helper.isValidHttpUrl(menu.url)"
              :icon="menu.iconClass ? menu.iconClass : 'remove'"
              :key="`menu-${index}`"
              :index="index"
              :style="`color: ${menu.color}`"
              :href="menu.url"
              :target="menu.target"
            >
              <span class="hide-in-minisidebar">{{ menu.title }}</span>
            </badaso-sidebar-item>
            <badaso-sidebar-item
              v-else
              :icon="menu.iconClass ? menu.iconClass : 'remove'"
              :key="`menu-${index}`"
              :index="index"
              :style="`color: ${menu.color}`"
              :to="menu.url"
              :target="menu.target"
            >
              <span class="hide-in-minisidebar">{{ menu.title }}</span>
            </badaso-sidebar-item>
          </div>
        </template>
      </badaso-sidebar-group>
    </vs-sidebar>
  </div>
</template>

<script>
import _ from "lodash";

export default {
  name: "SideBar",
  components: {},
  props: {
    parent: {
      type: String,
    },
    index: {
      default: null,
      type: [String, Number],
    },
    doNotClose: {
      default: false,
      type: Boolean,
    },
    view: {
      type: String,
      default: "desktop",
    },
  },
  data: () => ({
    sidebarModel: true,
    windowWidth: window.innerWidth,
    prefix: process.env.MIX_ADMIN_PANEL_ROUTE_PREFIX
      ? process.env.MIX_ADMIN_PANEL_ROUTE_PREFIX
      : "badaso-admin",
    // mainMenu: [],
  }),
  computed: {
    //This is for mobile trigger
    isSidebarActive: {
      get() {
        return this.$store.state.badaso.isSidebarActive;
      },
      set(val) {
        this.$store.commit("badaso/IS_SIDEBAR_ACTIVE", val);
      },
    },
    reduceSidebar: {
      get() {
        return this.$store.state.badaso.reduceSidebar;
      },
    },
    mainMenu: {
      get() {
        return this.$store.getters["badaso/getMenu"];
      },
    },
    configurationMenu: {
      get() {
        return this.$store.getters["badaso/getConfigurationMenu"];
      },
    },
    user: {
      get() {
        let user = this.$store.getters["badaso/getUser"];
        return user;
      },
    },
    getLocale: {
      get() {
        return this.$store.getters["badaso/getLocale"];
      },
    },
    selectedLang: {
      get() {
        let selected = this.$store.getters["badaso/getSelectedLocale"];
        if (selected.key) {
          selected = selected.key;
        }
        return selected;
      },
      set(val) {
        this.setLocale(_.find(this.getLocale, ["key", val]));
      },
    },
  },
  methods: {
    open(url) {
      if (!this.doNotClose) {
        this.isSidebarActive = false;
      }
      window.open(url);
    },
    logout() {
      this.$api.badasoAuth
        .logout()
        .then((response) => {
          localStorage.clear();
          this.$router.push({ name: "AuthLogin" });
        })
        .catch((error) => {
          this.$vs.notify({
            title: this.$t("alert.danger"),
            text: error.message,
            color: "danger",
          });
        });
    },
    setLocale(item) {
      this.$i18n.locale = item.key;
      this.$store.commit("badaso/SET_LOCALE", item);
    },
  },
  mounted() {
    this.$store.commit("badaso/FETCH_MENU");
    this.$store.commit("badaso/FETCH_CONFIGURATION_MENU");
  },
};
</script>
