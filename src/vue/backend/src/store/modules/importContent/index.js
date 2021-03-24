import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    mainLoading: false,
    listings: {
        unimported: [],
        imported: []
    },
    agents: {
        unimported: [],
        imported: []
    }
}

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state
}
