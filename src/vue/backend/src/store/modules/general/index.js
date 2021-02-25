import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    apiKey: '',
    guidedSetupSteps: [
        { name: '1. Connect', icon: 'link', total: 2, active: 1 },
        { name: '2. Your Listings', icon: 'list', total: 2, active: 0 },
        { name: '3. Agents', icon: 'users', total: 2, active: 0 },
        { name: '4. Social', icon: 'thumbs-up', total: 1, active: 0 }
    ],
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
