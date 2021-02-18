import * as types from '../../common/mutationTypes'

export default {
    [types.SET_ITEM] (state, payload) {
        state[payload.key] = payload.value
    }
}
