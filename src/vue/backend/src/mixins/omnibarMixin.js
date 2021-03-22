export default {
    methods: {
        omnibarMLSStateChange (payload) {
            // payload: single MLS object, new selected value, key of object, full mlsMembership array

            // Get a copy of the MLS object and replace the selected value
            const newValue = { ...payload.value[0] }
            newValue.selected = payload.value[1]

            // Get a copy of the full membership array
            const newArray = [...this.localStateValues.mlsMembership]

            // Replace the singular MLS object with the new one
            newArray.splice(payload.value[2], 1, newValue)
            return newArray
        },
        mlsChangeUpdate (event) {
            const updatedMLSMembership = this.omnibarMLSStateChange(event)
            // Assumes that the page guard mixin is also imported
            this.formUpdate({ key: 'mlsMembership', value: updatedMLSMembership })
        }
    }
}
