import actions from './actions'
import getters from './getters'

const state = {
    subscribed: false,
    enabled: false,
    autopublish: 'autopublish',
    postDay: 'sun',
    postType: 'post',
    authors: [],
    selectedAuthor: '',
    categories: [],
    selectedCategories: []
}

export default {
    namespaced: true,
    actions,
    getters,
    state
}
