/*
These constants reference state keys for now.
Use dot notation for nested object keys.
*/
const ENABLED = 'enabled'
const SUBSCRIBED = 'subscribed'

export const PRODUCT_REFS = {
    general: {
        module: 'general',
        term: 'isValid',
        termPath: 'apiKeyIsValid',
        get repo () {
            return `${this.module}Repository`
        }
    },
    listingsSettings: {
        module: 'listingsGeneral',
        term: ENABLED,
        termPath: 'enable',
        get repo () {
            return 'listingsSettingsRepository'
        }
    },
    agentSettings: {
        module: 'agentSettings',
        term: ENABLED,
        termPath: 'enable',
        get repo () {
            return `${this.module}Repository`
        }
    },
    socialPro: {
        module: 'socialPro',
        term: SUBSCRIBED,
        termPath: '',
        get repo () {
            return `${this.module}Repository`
        }
    },
    importContent: {
        module: 'importContent',
        termPath: '',
        get repo () {
            return `${this.module}Repository`
        }
    }
}
/* Todo: Clean this up. */
export const SOCIAL_PRO = 'socialPro.subscribed'
export const LISTINGS = 'listingsSettings.enabled'
export const AGENTS = 'agentSettings.enabled'
export const API_KEY = 'general.isValid'
