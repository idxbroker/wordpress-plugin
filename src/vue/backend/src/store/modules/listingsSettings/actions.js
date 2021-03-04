const saveListingsSettings = ({ commit }, payload) => {
    // To-Do: api call to corresponding endpoint
    console.log('Send the state changes here')
    return new Promise(resolve => setTimeout(resolve, 100))
}

const saveListingsGeneralSettings = () => {
    // To-Do: add actual endpoints
    return new Promise(resolve => setTimeout(resolve, 1000))
}

export default {
    saveListingsSettings,
    saveListingsGeneralSettings
}
