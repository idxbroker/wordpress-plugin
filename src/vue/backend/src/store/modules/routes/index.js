import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    routes: {},
    categoryKeys: []
}

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state
}
