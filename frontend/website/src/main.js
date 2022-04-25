import { createApp } from 'vue'
import * as vars from './global-vars.js'
import * as methods from './global-methods.js'
import App from './App.vue'
import router from './router'
import Axios from './HTTP.js'
import mixin from './mixin.js'
import moment from 'moment'

import mitt from 'mitt';
const emitter = mitt();

import Header from '@/components/Header.vue';
import Error from '@/components/Error.vue';

const app = createApp(App)

app.use(router)

app.mixin(mixin)

app.component('Header', Header)
app.component('Error', Error)

app.config.globalProperties.$http = Axios;
app.config.globalProperties.emitter = emitter;
app.config.globalProperties.moment = moment;

app.mount('#app')
