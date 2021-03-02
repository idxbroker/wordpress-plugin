import Vue from 'vue'
import Vuex from 'vuex'
import agentSettings from './modules/agentSettings'
import general from './modules/general'
import importContent from './modules/importContent'
import listingsSettings from './modules/listingsSettings'
import omnibar from './modules/omnibar'
import socialPro from './modules/socialPro'
import routes from './modules/routes'
Vue.use(Vuex)
const modules = {
    agentSettings,
    general,
    importContent,
    listingsSettings,
    omnibar,
    socialPro,
    routes
}

const store = new Vuex.Store({
    strict: process.env.NODE_ENV !== 'production',
    modules
})

export default store
