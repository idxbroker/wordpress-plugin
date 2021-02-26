import * as types from './mutationTypes'

const setItem = ({ commit }, payload = {}) => {
    commit(types.SET_ITEM, payload)
}

export default {
    setItem
}
