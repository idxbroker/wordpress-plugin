import * as types from '../../common/mutationTypes'

export default {
    // Will be filled in as development happens
    [types.SET_ITEM] (state, payload) {
        state[payload.key] = payload.value
    }
}
