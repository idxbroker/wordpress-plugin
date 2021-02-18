import * as types from '../../common/store/mutationTypes'

const generalSettingsStateChange = ({ commit }, payload) => {
    commit(types.SET_ITEM, {
        key: payload.key,
        value: payload.value
    })
}

export default {
    generalSettingsStateChange
}
