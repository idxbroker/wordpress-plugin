import * as types from '../../common/mutationTypes'

const generalSettingsStateChange = ({ commit }, payload) => {
    commit(types.SET_ITEM, {
        key: payload.key,
        value: payload.value
    })
}

export default {
    generalSettingsStateChange
}
