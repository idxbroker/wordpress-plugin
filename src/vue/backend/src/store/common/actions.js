import * as types from './mutation-types'

const setItem = ({ commit }, payload = {}) => {
    commit(types.SET_ITEM, payload)
}

export default {
    setItem
}
