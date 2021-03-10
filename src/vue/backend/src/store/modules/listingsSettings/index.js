import actions from './actions'
import getters from './getters'

const state = {
    enabled: true,
    currencyCodeSelected: 'none',
    currencySymbolSelected: 'none',
    defaultDisclaimer: '',
    numberOfPosts: '9',
    listingSlug: 'listings',
    defaultState: '',
    updateListings: 'update-all',
    soldListings: 'keep-all',
    automaticImport: false,
    defaultListingTemplateSelected: '',
    importedListingsAuthorSelected: '',
    // options will be returned with client data, and will be set in an action on the parent template.
    defaultListingTemplateOptions: [],
    importedListingsAuthorOptions: [],
    displayIDXLink: false,
    importTitle: '{{address}}',
    advancedFieldData: false,
    displayAdvancedFields: false,
    deregisterMainCss: false,
    deregisterWidgetCss: false,
    sendFormSubmission: true,
    formShortcode: '',
    googleMapsAPIKey: '',
    useCustomWrapper: false,
    wrapperStart: '',
    wrapperEnd: '',
    deletePluginDataOnUninstall: false
}

export default {
    namespaced: true,
    actions,
    getters,
    state
}
