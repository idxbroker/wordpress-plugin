import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    reCAPTCHA: false,
    updateFrequency: { value: '3', label: '3 minutes' },
    wrapperName: 'idx-wp-'
}

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state
}
