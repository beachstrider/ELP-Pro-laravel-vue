import Vue from 'vue'
import Vuex from 'vuex'
import user from './user'
import settings from './settings'
import global from './global'

Vue.use(Vuex)

export default new Vuex.Store({
    modules: {
        user,
        settings,
        global
    },
    state: {},
    mutations: {},
    actions: {},
})
