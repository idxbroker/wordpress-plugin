const state = {
    updateListings: 'update-all',
    soldListings: 'keep-all',
    automaticImport: false,
    defaultListingTemplateSelected: '',
    importedListingsAuthorSelected: '',
    defaultListingTemplateOptions: [],
    importedListingsAuthorOptions: [],
    displayIDXLink: false,
    importTitle: '{{address}}',
    advancedFieldData: false,
    displayAdvancedFields: false
}
export default {
    namespaced: true,
    state
}
