import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    deregisterMainCss: false,
    numberOfPosts: '',
    directorySlug: '',
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
