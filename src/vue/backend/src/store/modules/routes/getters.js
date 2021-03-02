import { filterRequires } from '@/utilities'
export default {
    navigationRoutes: (state, getters, rootState) => {
        return state.categoryKeys.reduce((final, key) => {
            const copy = { ...state.routes[key] }
            if (copy.routes) {
                copy.routes = copy.routes.map(id => state.routes[id]).filter((route) => filterRequires(route, rootState))
            }
            final.push(copy)
            return final
        }, []).filter((result) => filterRequires(result, rootState))
    }
}
