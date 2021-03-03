import * as types from '../../common/mutationTypes'

const listingsSettingsStateChange = ({ commit }, payload) => {
    commit(types.SET_ITEM, {
        key: payload.key,
        value: payload.value
    })
}
const saveListingsSettings = ({ commit }, payload) => {
    // To-Do: api call to corresponding endpoint
    console.log('Send the state changes here')
    return new Promise(resolve => setTimeout(resolve, 100))
}

export default {
    listingsSettingsStateChange,
    saveListingsSettings
}
