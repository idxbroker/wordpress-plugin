import * as types from '../../common/mutationTypes'

const generalSettingsStateChange = ({ commit }, payload) => {
    commit(types.SET_ITEM, {
        key: payload.key,
        value: payload.value
    })
}

const verifyAPIkey = () => {
    // To-Do: Do API call here
    // This is a placeholder to mimic response time
    return new Promise(resolve => setTimeout(resolve, 5000))
}

const saveGeneralSettings = () => {
    // To-Do: add actual endpoints
    // GET /idxbroker/v1/admin/settings/general
    // return {
    //     apiKey,
    //     wrapperName,
    //     reCAPTCHA,
    //     updateFrequency
    // }
    // POST /idxbroker/v1/admin/settings/general
    // Include only what needs to be updated
    // Request Body {
    //     apiKey,
    //     wrapperName,
    //     reCAPTCHA,
    //     updateFrequency
    // }
    // return 200 or error message
    return new Promise(resolve => setTimeout(resolve, 1000))
}

export default {
    generalSettingsStateChange,
    saveGeneralSettings,
    verifyAPIkey
}
