import * as types from '../../common/mutationTypes'

const generalSettingsStateChange = ({ commit }, payload) => {
    commit(types.SET_ITEM, {
        key: payload.key,
        value: payload.value
    })
}

const verifyAPIkey = () => {
    // To-Do: Do API call here
    // This is a placeholder to mimic response time
    return new Promise(resolve => setTimeout(resolve, 5000))
}

const saveOmnibarSettings = () => {
    // To-Do: add actual endpoints
    // GET /idxbroker/v1/admin/settings/omnibar
    // return {
    //     cityListOptions,
    //     cityListSelected,
    //     countyListOptions,
    //     countyListSelected,
    //     postalCodeListOptions,
    //     postalCodeSelected,
    //     defaultPropertyTypeOptions,
    //     defaultPropertyTypeSelected,
    //     mlsMembership: [
    //         {
    //             label: 'Regional Multiple Listings Service',
    //             value: 'a001',
    //             propertyTypes: [
    //                 { value: 'single-family', label: 'Single Family'},
    //                 { value: 'commercial', label: 'Commercial'},
    //                 etc
    //             ],
    //             selected: 'single-family'
    //         },
    //         etc
    //     ],
    //     autofillMLSOptions,
    //     autofillMLSSelected,
    //     customFieldsSelected,
    //     customFieldsOptions,
    //     customPlaceholder,
    //     defaultSortOrderSelected
    // }
    // POST /idxbroker/v1/admin/settings/omnibar
    // Include only what needs to be updated
    // Request Body {
    //     cityListSelected,
    //     countyListSelected,
    //     postalCodeSelected,
    //     defaultPropertyTypeSelected,
    //     mlsMembership: [
    //         {
    //             value: 'a001',
    //             selected: 'commercial'
    //         },
    //         etc
    //     ],
    //     autofillMLSSelected,
    //     customFieldsSelected,
    //     customPlaceholder,
    //     defaultSortOrderSelected
    // }
    // return 200 or error message tbd
    return new Promise(resolve => setTimeout(resolve, 1000))
}

export default {
    generalSettingsStateChange,
    saveOmnibarSettings,
    verifyAPIkey
}
