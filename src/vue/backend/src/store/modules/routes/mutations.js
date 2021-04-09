export default {
    SET_OBJECT: (state, { key, value } = {}) => {
        state[key] = { ...state[key], ...value }
    },
    SET_ITEM: (state, { key, value } = {}) => {
        state[key] = value
    },
    SET_SOCIAL_PRO: (state, { key, value } = {}) => {
        state.routes[key] = value
    },
    TOGGLE_ROUTE: (state, { key } = {}) => {
        const expanded = Object.keys(state.routes).filter(key => state.routes[key].collapsed === false)
        if (expanded.length) {
            if (expanded[0] !== key) {
                state.routes[expanded[0]].collapsed = true
            }
        }
        state.routes[key].collapsed = !state.routes[key].collapsed
    },
    COLLAPSE_ROUTES: (state) => {
        Object.keys(state.routes)
            .filter(key => state.routes[key].collapsed === false)
            .forEach(key => {
                state.routes[key].collapsed = true
            })
    }
}
