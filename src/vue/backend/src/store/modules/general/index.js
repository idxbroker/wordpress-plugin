import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    enableRecaptcha: false,
    updateFrequency: { name: '3 minutes' },
    websiteWrapper: 'idx-wp-'
}

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state
}
