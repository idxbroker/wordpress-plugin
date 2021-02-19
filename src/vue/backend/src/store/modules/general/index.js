import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    reCAPTCHA: false,
    updateFrequency: { value: '', label: '' },
    wrapperName: ''
}

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state
}
