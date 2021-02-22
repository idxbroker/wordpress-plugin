import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    currencyCodeSelected: { value: null, label: 'Select Currency Code' },
    currencySymbolSelected: { value: null, label: 'Select Currency Symbol' },
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
