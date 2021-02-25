import * as types from './mutationTypes'

export default {
    [types.SET_ITEM] (state, { key, value } = {}) {
        state[key] = value
    }
}
