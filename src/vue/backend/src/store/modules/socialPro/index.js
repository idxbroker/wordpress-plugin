import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    enableSyndication: false,
    autopublishArticles: { name: 'Autopublish' },
    postDay: { name: 'Sunday' },
    postType: { name: 'Post' }
}

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state
}
