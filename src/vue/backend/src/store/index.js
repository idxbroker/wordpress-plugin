import Vue from 'vue'
import Vuex from 'vuex'
import agentSettings from './modules/agentSettings'
import alerts from './modules/alerts'
import general from './modules/general'
import importContent from './modules/importContent'
import listingsSettings from './modules/listingsSettings'
import omnibar from './modules/omnibar'
import socialPro from './modules/socialPro'
import routes from './modules/routes'
import progressStepper from './modules/progressStepper'

// Common actions
import commonActions from './common/actions'
import commonMutations from './common/mutations'

Vue.use(Vuex)
const modules = {
    agentSettings,
    alerts,
    general,
    importContent,
    listingsSettings,
    omnibar,
    socialPro,
    routes,
    progressStepper
}
const applyCommonPieces = (storeModule) => {
    storeModule.actions = { ...commonActions, ...storeModule.actions }
    storeModule.mutations = { ...commonMutations, ...storeModule.mutations }
    return storeModule
}

const bulkApplyCommonPieces = (storeModules) => {
    return Object.keys(storeModules).reduce((acc, cur) => {
        acc[cur] = applyCommonPieces(storeModules[cur])
        return acc
    }, {})
}
const store = new Vuex.Store({
    strict: process.env.NODE_ENV !== 'production',
    modules: bulkApplyCommonPieces(modules)
})

export default store
