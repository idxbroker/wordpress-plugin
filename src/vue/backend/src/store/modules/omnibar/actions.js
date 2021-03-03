import * as types from '../../common/mutationTypes'

const omnibarMLSStateChange = ({ commit }, payload) => {
    // payload: single MLS object, new selected value, key of object, full mlsMembership array

    // Get a copy of the MLS object and replace the selected value
    const newValue = { ...payload.value[0] }
    newValue.selected = payload.value[1]

    // Get a copy of the full membership array
    const newArray = [...payload.value[3]]

    // Replace the singular MLS object with the new one
    newArray.splice(payload.value[2], 1, newValue)

    commit(types.SET_ITEM, {
        key: payload.key,
        value: newArray
    })
}
export default {
    omnibarMLSStateChange
}
