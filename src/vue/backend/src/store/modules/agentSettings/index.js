import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    enabled: false,
    deregisterMainCss: false,
    numberOfPosts: '9',
    directorySlug: 'employees',
    wrapperStart: '',
    wrapperEnd: ''
}

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state
}
