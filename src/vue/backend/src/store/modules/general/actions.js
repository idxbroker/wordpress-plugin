const verifyAPIkey = () => {
    // To-Do: Do API call here
    // This is a placeholder to mimic response time
    return new Promise(resolve => setTimeout(resolve, 5000))
}

const saveGeneralSettings = () => {
    // To-Do: add actual endpoints
    return new Promise(resolve => setTimeout(resolve, 1000))
}

export default {
    saveGeneralSettings,
    verifyAPIkey
}
