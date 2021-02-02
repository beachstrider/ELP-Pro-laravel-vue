import 'ant-design-vue/lib/style/index.less' // antd core styles
import './components/kit/vendors/antd/themes/default.less' // default theme antd components
import './components/kit/vendors/antd/themes/dark.less' // dark theme antd components
import './global.scss' // app & third-party component styles

import Vue from 'vue'
import VuePageTitle from 'vue-page-title'
import NProgress from 'vue-nprogress'
import VueLayers from 'vuelayers'
import BootstrapVue from 'bootstrap-vue'
import App from './App.vue'
import router from './router'
import store from './store'
import './antd'
import './registerServiceWorker'
import SUpload from './components/custom/SUpload'
import SMulitpleUpload from './components/custom/SMulitpleUpload'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import ClipLoader from 'vue-spinner/src/ClipLoader'
import Datepicker from 'vuejs-datepicker'
import { i18n } from './localization'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'
import _ from 'lodash';

import {
    formatDate,
    handleSyncRequestLoader,
    hasAllPermissions,
    hasAnyPermission,
    hasPermission,
    hasRole,
    utcDateToLocalDate,
    dateToUtcDate,
} from "./util/Utils";
Vue.use(BootstrapVue)
Vue.use(VueLayers)
Vue.component('v-select', vSelect)
Vue.component('clip-loader', ClipLoader)
Vue.component('upload', SUpload)
Vue.component('multi-upload', SMulitpleUpload)
Vue.component('datepicker', Datepicker)

Vue.use(NProgress)
Vue.use(VuePageTitle, {
  prefix: 'Emil Frey | ',
  router,
})

Vue.config.productionTip = false
const nprogress = new NProgress({ parent: 'body' })

Vue.prototype.$global = {
    hasPermission:(permission) => hasPermission(permission),
    hasAnyPermission:(permission) => hasAnyPermission(permission),
    hasAllPermissions:(permission) => hasAllPermissions(permission),
    hasRole:(role) => hasRole(role),
    dateFormat:(date, format = 'DD-MM-YYYY') => ((date) ? formatDate(date, format) : null),
    utcDateToLocalDate:(date, toFormat = 'DD-MM-YYYY hh:mm A', fromFormat = 'YYYY-MM-DD HH:mm:ss') => utcDateToLocalDate(date, toFormat, fromFormat),
    dateToUtcDate: (date, fromFormat = 'YYYY-MM-DD[T]HH:mm:ss', toFormat = 'YYYY-MM-DD[T]HH:mm:ss') => dateToUtcDate(date, toFormat, fromFormat),
    baseUrl: process.env.VUE_APP_API_URL,
};
Vue.prototype._ = _

new Vue({
    i18n,
    router,
    store,
    nprogress,
    render: h => h(App),
    created() {
        handleSyncRequestLoader(store, process.env.VUE_APP_API_URL);
    }
}).$mount('#app')
