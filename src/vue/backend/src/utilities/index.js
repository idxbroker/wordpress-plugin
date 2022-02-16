/* Common utilities to keep our code DRY. */
import _get from 'lodash.get'
/**
 * Filter Requires
 * Usage: Pass an array called "requires" that is mapped to a store module's state.key values using dot notation, which
 * is then extracted out of the state using lodash's _get method.
 * Example: route.requires = ["general.apiKey", "listingsSettings.enabled"]
 *
 * @param {object} routeObject The route object to be filtered upon.
 * @param {object} rootState The rootState object from vuex - or state if using outside of a getter.
 *
 * @returns {boolean} True if the filtered requirements array length matches the original requirements array.
 */
export const filterRequires = (routeObject, rootState) => {
    if (routeObject.requires) {
        const result = routeObject.requires.filter(key => _get(rootState, key))
        return result.length === routeObject.requires.length
    }
    return true
}

// Decode HTML Entities in MLS names when displayed through text interpolation.
export const decodeEntities = string => {
    return String(string).replace(/&reg;|&reg/gi, '®').replace(/&copy;|&copy/gi, '©')
}
