import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    reCAPTCHA: false,
    updateFrequency: 'five_minutes',
    wrapperName: ''
}

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state
}
