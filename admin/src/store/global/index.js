import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default {
    state: {
        pendingRequests: 0,
    },
    mutations: {
        ADD_PENDING_REQUESTS(state, payload) {
            state.pendingRequests = state.pendingRequests + 1;
        },

        REMOVE_PENDING_REQUESTS(state, payload) {
            state.pendingRequests = (state.pendingRequests > 0) ? state.pendingRequests - 1 : 0;
        },
    },
    actions: {
        addPendingRequests(context, payload) {
            context.commit('ADD_PENDING_REQUESTS', payload);
        },

        removePendingRequests(context, payload) {
            context.commit('REMOVE_PENDING_REQUESTS', payload);
        },
    },
    getters: {
        pendingRequests: state => state.pendingRequests,
    },
}
