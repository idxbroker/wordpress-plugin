import * as types from './mutation-types'

export default {
    [types.SET_ITEM] (state, { key, value } = {}) {
        state[key] = value
    }
}
