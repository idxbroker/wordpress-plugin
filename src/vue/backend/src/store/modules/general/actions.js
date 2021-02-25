import * as types from '../../common/mutationTypes'

const generalSettingsStateChange = ({ commit }, payload) => {
    commit(types.SET_ITEM, {
        key: payload.key,
        value: payload.value
    })
}

const promiseWithTimeout = () => {
    return new Promise(resolve => setTimeout(resolve, 5000))
}

export default {
    generalSettingsStateChange,
    promiseWithTimeout
}
