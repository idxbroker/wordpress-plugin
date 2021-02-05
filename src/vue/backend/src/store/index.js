import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        relatedLinks: [
            {
                href: '#link1',
                text: 'Where can I find my API key?'
            },
            {
                href: '#link2',
                text: 'IDX Broker Middleware'
            },
            {
                href: '#link3',
                text: 'Sign up for IDX Broker'
            }
        ]
    },
    mutations: {
    },
    actions: {
    },
    modules: {
    }
})
