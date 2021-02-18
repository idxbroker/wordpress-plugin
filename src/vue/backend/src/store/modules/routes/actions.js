import { navLinks } from '../../../data/navlinks'
import { normalize } from 'normalizr'
import routeSchema from '../../../data/schema'

export default {
    gatherRoutes: ({ dispatch }) => {
        const { entities = {}, result = [] } = normalize(navLinks, routeSchema)
        Object.keys(entities).forEach(key => {
            dispatch('setRoutes', {
                key: key,
                value: entities[key]
            })
        })
        dispatch('setRouteKeys', { key: 'categoryKeys', value: result })
    },
    setRoutes: ({ commit }, payload) => {
        commit('SET_OBJECT', payload)
    },
    setRouteKeys: ({ commit }, payload = []) => {
        commit('SET_ITEM', payload)
    },
    expandRoute: ({ commit }, payload) => {
        commit('TOGGLE_ROUTE', { key: payload })
    },
    collapseRoutes: ({ commit }) => {
        commit('COLLAPSE_ROUTES')
    },
    toggleSidebar: ({ commit }, payload) => {
        commit('SET_ITEM', payload)
    }
}
