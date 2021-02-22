import actions from './actions'
import getters from './getters'
import mutations from './mutations'

const state = {
    updateListings: 'update-all',
    soldListings: 'keep-all',
    automaticImport: false,
    defaultListingTemplateSelected: { value: null, label: 'Select a Template' },
    importedListingsAuthorSelected: { value: null, label: 'Select an Author' },
    // options will be returned with client data, and will be set in an action on the parent template.
    defaultListingTemplateOptions: [{ value: null, label: 'Select a Template' }],
    importedListingsAuthorOptions: [{ value: null, label: 'Select an Author' }],
    displayIDXLink: false,
    importTitle: '{{address}}',
    advancedFieldData: false,
    displayAdvancedFields: false
}

export default {
    namespaced: true,
    actions,
    getters,
    mutations,
    state
}
