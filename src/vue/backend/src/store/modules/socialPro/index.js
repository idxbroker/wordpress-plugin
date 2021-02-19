import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    enableSyndication: false,
    autopublish: { label: 'Autopublish', value: 'autopublish' },
    postDay: { label: 'Sunday', value: 'sun' },
    postType: { label: 'Post', value: 'post' }
}

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state
}
