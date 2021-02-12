export const API_SETTINGS = {
    withCredentials: true,
    headers: {}
    // timeout: 5000
}

export const API_ENDPOINTS = {
    base: '/mgmt',
    get navigation () {
        return `${this.base}/navigation`
    },
    get ajax () {
        return `${this.base}/ajax`
    },
    get api () {
        return `${this.base}/api`
    }
}

// This object defines how we translate the returned category API call.
export const navigationModelTranslation = {
    categories: { key: 'routes' },
    pages: [
        { from: 'display', to: 'label' },
        { from: 'page', to: 'route' }
    ],
    subpages: [
        { from: 'display', to: 'label' },
        { from: 'page', to: 'route' }
    ]
}

export const CACHE_SETTINGS = {
    key: 'idx-middleware',
    storageLength: 4 // minutes
}

export const NAVIGATION_ROUTE_SETTINGS = {
    /**
     * @todo Try to implement a better solution than a hard-coded development build setting.
     *
     * Possible Options:
     *  - .env.development.local
     *  - caveat: environment variables loaded before serve process, so it can't be changed and reloaded fluidly.
     */
    currentPage: process.env.NODE_ENV === 'production' ? window.location.pathname : '/mgmt/mls/a001/mapper'
}

// Mobile Width used in js to determine if we're in mobile mode. A scss variable is in the _variables
// file that would need to be adjusted to match this.
export const mobileWidth = 860

// Client-level account types.
export const PLAT = 20
export const SUPER_LITE = 22
export const HOME = 23
export const PLAT_LEGACY = 24
export const LITE = 25
export const HOME_LEGACY = 26
export const MLS = 29

export const LEVELS = {
    // For support of existing 'isPlatinum'-like method in a getter.
    IS_PLATINUM: [PLAT, HOME, PLAT_LEGACY, HOME_LEGACY],
    // Account levels that include sold data.
    SOLD_INCLUDED: [PLAT, HOME]
}
