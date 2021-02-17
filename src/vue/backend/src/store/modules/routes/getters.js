export default {
    navigationRoutes: (state) => {
        return state.categoryKeys.reduce((final, key) => {
            const copy = { ...state.routes[key] }
            if (copy.routes) {
                copy.routes = copy.routes.map(id => state.routes[id])
            }
            final.push(copy)
            return final
        }, [])
    }
}
