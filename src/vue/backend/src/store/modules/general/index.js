import actions from './actions'
import getters from './getters'

const state = {
    apiKey: '',
    reCAPTCHA: false,
    updateFrequency: 'five_minutes',
    wrapperName: ''
}

export default {
    namespaced: true,
    actions,
    getters,
    state
}
