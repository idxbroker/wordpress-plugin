import actions from './actions'
import getters from './getters'
const state = {
    guidedSetupSteps: [
        { name: '1. Connect', icon: 'link', total: 4, active: 0 },
        { name: '2. Your Listings', icon: 'list', total: 5, active: 0 },
        { name: '3. Agents', icon: 'users', total: 3, active: 0 },
        { name: '4. Social', icon: 'thumbs-up', total: 3, active: 0, hideProgress: true }
    ],
    hasChanges: false,
    general: {
        changes: {},
        module: 'general',
        path: ''
    },
    omnibar: {
        changes: {},
        module: 'omnibar',
        path: ''
    },
    listingsGeneral: {
        changes: {},
        module: 'listingsSettings',
        path: 'general'
    },
    listingsIdx: {
        changes: {},
        module: 'listingsSettings',
        path: 'idx'
    },
    listingsAdvanced: {
        changes: {},
        module: 'listingsSettings',
        path: 'advanced'
    },
    agentSettings: {
        changes: {},
        module: 'agentSettings',
        path: ''
    },
    socialPro: {
        changes: {},
        module: 'socialPro',
        path: ''
    }
}

export default {
    namespaced: true,
    state,
    actions,
    getters
}
