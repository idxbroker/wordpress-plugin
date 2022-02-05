const state = {
    mainLoading: false,
    listings: {
        unimported: [],
        imported: []
    },
    agents: {
        unimported: [],
        imported: []
    }
}

export default {
    namespaced: true,
    state
}
