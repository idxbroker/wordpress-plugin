const state = {
    restrictedByBeta: true,
    optedInBeta: false,
    subscribed: false,
    enabled: true,
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
    state
}
