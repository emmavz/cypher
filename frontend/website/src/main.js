import { createApp } from "vue/dist/vue.esm-bundler";
import * as vars from "./global-vars.js";
import * as methods from "./global-methods.js";
import App from "./App.vue";
import router from "./router";
import { axios, instance } from "./HTTP.js";
import mixin from "./mixin.js";
import moment from "moment";
import vClickOutside from "click-outside-vue3";
import VueClipboard from "vue-clipboard2";
import VueSocialSharing from "vue-social-sharing";

import mitt from "mitt";
const emitter = mitt();

import Header from "@/components/Header.vue";
import Error from "@/components/Error.vue";

const app = createApp(App);
window.vue = app;

app.use(router);
app.use(VueClipboard);
app.use(vClickOutside);
app.use(VueSocialSharing);

app.mixin(mixin);

app.component("Header", Header);
app.component("Error", Error);

app.config.globalProperties.$http = instance;
app.config.globalProperties.$axios = axios;
app.config.globalProperties.emitter = emitter;
app.config.globalProperties.moment = moment;

app.mount("#app");
