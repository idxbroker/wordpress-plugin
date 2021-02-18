import * as types from '../../common/store/mutationTypes'

export default {
    [types.SET_ITEM] (state, payload) {
        state[payload.key] = payload.value
    }
}
