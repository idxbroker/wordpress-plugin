import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    currencyCodeSelected: { value: '', label: '' },
    currencySymbolSelected: { value: '', label: '' },
    defaultDisclaimer: '',
    numberOfPosts: '',
    listingSlug: '',
    defaultState: ''
}

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state
}
