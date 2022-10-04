<template>
    <idx-block
        tag="fieldset"
        :className="{
            'form-content': true,
            'form-content--disabled': formDisabled
        }">
        <p>
            Omnibar is a powerful search tool, which allows
            for a simple search bar to automatically search
            for properties in your MLS with a single query.
        </p>
        <hr/>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">City, County, and Postal Code Lists</idx-block>
            <p>Only locations in these lists will return results.</p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">{{ labels.cityListLabel }}</idx-form-label>
            <idx-custom-select
                placeholder="Select"
                :ariaLabel="labels.cityListLabel"
                :selected="cityListSelected"
                :options="cleanOptionsList(cityListOptions)"
                @selected-item="$emit('form-field-update', { key: 'cityListSelected', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">{{ labels.countyListLabel }}</idx-form-label>
            <idx-custom-select
                placeholder="Select"
                :ariaLabel="labels.countyListLabel"
                :selected="countyListSelected"
                :options="cleanOptionsList(countyListOptions)"
                @selected-item="$emit('form-field-update', { key: 'countyListSelected', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">{{ labels.postalCodeListLabel }}</idx-form-label>
            <idx-custom-select
                placeholder="Select"
                :ariaLabel="labels.postalCodeListLabel"
                :selected="postalCodeSelected"
                :options="cleanOptionsList(postalCodeListOptions)"
                @selected-item="$emit('form-field-update', { key: 'postalCodeSelected', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
        <hr/>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Property Type</idx-block>
            <p>Choose the property type for default and custom fields.</p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">{{ labels.defaultPropertyTypeLabel }}</idx-form-label>
            <idx-custom-select
                placeholder="Choose the property type for default and custom fields"
                :ariaLabel="labels.defaultPropertyTypeLabel"
                :selected="defaultPropertyTypeSelected"
                :options="defaultPropertyTypeOptions"
                @selected-item="$emit('form-field-update', { key: 'defaultPropertyTypeSelected', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
        <idx-block className="omnibar-form__field-subset">
            <idx-block className="form-content__header">
                <idx-block className="form-content__label">MLS Specific Property Type</idx-block>
                <p>Used for custom field searches and addresses</p>
            </idx-block>
            <idx-form-group
                v-for="(mls, key) in mlsMembership"
                :key="mls.value"
            >
                <idx-form-label customClass="form-content__label">{{ decodeEntities(mls.label) }}</idx-form-label>
                <idx-custom-select
                    :ariaLabel="mls.label"
                    :selected="mls.selected === '' ? undefined : mls.selected"
                    :options="mls.propertyTypes"
                    @selected-item="$emit('form-field-update-mls-membership', { key: 'mlsMembership', value: [ mls, $event.value, key ] })"
                ></idx-custom-select>
            </idx-form-group>
        </idx-block>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Addresses</idx-block>
            <p>Choose which MLS is included in the address autofill. Addresses will only be included from the selected property types.</p>
        </idx-block>
        <idx-form-group>
            <idx-form-label
                :customClass="{
                    ['form-content__label']: true,
                    ['form-content--disabled']: Object.keys(mlsSpecificPropTypes).length === 0
                }"
            >{{ labels.addressAutofillLabel }}</idx-form-label>
            <idx-input-tag-autocomplete
                placeholder="Select MLS Source"
                :previousSelections="autofillMLSSelected"
                :resultsList="mlsNamesList"
                @tag-list="updateCustomTags($event, 'autofillMLSSelected')"
            ></idx-input-tag-autocomplete>
        </idx-form-group>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Custom Fields</idx-block>
            <p>By default the omnibar searches by City, County, Postal Code, or Listing ID. Add up to 10 custom fields to be used as well.</p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">Add Custom Fields</idx-form-label>
            <idx-block
                v-if="invalidCustomTagsCheck.length > 0"
                className="field__warning"
                tag="ul"
            >
                The following {{ this.invalidCustomTagsCheck.length > 1 ? 'tags are' : 'tag is' }} not in the selected property type:
                <idx-block
                    v-for="invalid in invalidCustomTagsCheck"
                    :key="invalid.value"
                    tag="li"
                >
                    {{ invalid.label }}
                </idx-block>
                Please choose a Custom Field within the selected MLS Specific Property Type.
            </idx-block>
            <idx-input-tag-autocomplete
                placeholder="Enter List Item"
                :limit="10"
                :previousSelections="customFieldsSelectedCleaned"
                :resultsList="customFieldsOptionsCleaned"
                @tag-list="updateCustomTags($event, 'customFieldsSelected')"
            ></idx-input-tag-autocomplete>
        </idx-form-group>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Custom Placeholder</idx-block>
            <p>This is a placeholder for the main input of Omnibar Widgets.<br>
            Examples: “Search for Properties”, “Location, School, Address, or Listing ID”.</p>
        </idx-block>
        <idx-form-group>
            <idx-form-label :target="`${$idxStrap.prefix}customPlaceholder`" customClass="form-content__label">Custom Placeholder</idx-form-label>
            <idx-form-input
                type="text"
                customClass=""
                :id="`${$idxStrap.prefix}customPlaceholder`"
                :value="customPlaceholder"
                @change="$emit('form-field-update', { key: 'customPlaceholder', value: $event.target.value })"
            ></idx-form-input>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">
                <idx-block tag="h2" className="form-content__title">{{ labels.sortOrderLabel }}</idx-block>
                <p>The default sort order for results pages.</p>
            </idx-form-label>
            <idx-custom-select
                :ariaLabel="labels.sortOrderLabel"
                :selected="defaultSortOrderSelected"
                :options="sortOrderOptions"
                @selected-item="$emit('form-field-update', { key: 'defaultSortOrderSelected', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
    </idx-block>
</template>
<script>
import { decodeEntities } from '@/utilities'
export default {
    name: 'omnibar-form',
    props: {
        cityListOptions: {
            type: Array,
            default: () => []
        },
        cityListSelected: {
            type: String,
            default: ''
        },
        countyListOptions: {
            type: Array,
            default: () => []
        },
        countyListSelected: {
            type: String,
            default: ''
        },
        postalCodeListOptions: {
            type: Array,
            default: () => []
        },
        postalCodeSelected: {
            type: String,
            default: ''
        },
        defaultPropertyTypeSelected: {
            type: String,
            default: ''
        },
        mlsMembership: {
            type: Array,
            default: () => []
        },
        autofillMLSSelected: {
            type: Array,
            default: () => []
        },
        customFieldsSelected: {
            type: Array,
            default: () => []
        },
        customFieldsOptions: {
            type: Array,
            default: () => []
        },
        customPlaceholder: {
            type: String,
            default: ''
        },
        defaultSortOrderSelected: {
            type: String,
            default: ''
        },
        formDisabled: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        mlsNamesList () {
            const names = []
            for (const x in this.mlsMembership) {
                names.push({ value: this.mlsMembership[x].value, label: decodeEntities(this.mlsMembership[x].label) })
            }
            return names
        },
        invalidCustomTagsCheck () {
            // Returns the custom fields that are not valid given the property types selected
            // Set an empty invalid tags array
            const invalidTags = []
            // Loop through the cleaned list of the selected custom fields
            for (const x in this.customFieldsSelectedCleaned) {
                // Check if the selected custom field's property type is not in the list of selected property types
                if (this.mlsSpecificPropTypes[this.customFieldsSelectedCleaned[x].idxID] !== this.customFieldsSelectedCleaned[x].mlsPtID) {
                    // Add the item to the invalid tags array
                    invalidTags.push(this.customFieldsSelectedCleaned[x])
                }
            }
            // return the list of invalid custom tags
            return invalidTags
        },
        mlsSpecificPropTypes () {
            // A list of the property types selected in the mls specific property types fields
            const selections = {}
            // For MLS in the mls membership object
            for (const x in this.mlsMembership) {
                // Add the selected property type to the object
                // ex: a001: 'Residential'
                if (this.mlsMembership[x].selected) {
                    selections[this.mlsMembership[x].value] = this.mlsMembership[x].selected
                }
            }
            // return the selected options
            return selections
        },
        customFieldsOptionsCleaned () {
            // Take the custom field options and modify the options to contain
            // a user friendly label and the IDX ID of the mls it is a part of
            const options = []
            // Loop through the custom field options prop
            for (let x = 0; x < this.customFieldsOptions.length; x++) {
                // Get the object containing the information about the specific
                // MLS this option is in
                const MLSName = this.findMLSName(this.customFieldsOptions[x].idxID)
                // For all the fields available, we want the options to have the
                // MLS value and the user friendly label
                this.customFieldsOptions[x].fieldNames.forEach(option => {
                    // If the option is one of the selected mls specific property types
                    if (option.mlsPtID === this.mlsSpecificPropTypes[MLSName.value]) {
                        // Add the option with a user friendly label and MLS value
                        options.push({
                            ...this.addCleanLabel(option, MLSName),
                            idxID: MLSName.value
                        })
                    }
                })
            }
            // The options with the new data
            return options
        },
        customFieldsSelectedCleaned () {
            // Clean the incoming selected fields and transform their label to the
            // user friendly label
            return this.customFieldsSelected.map(x => {
                const MLSName = this.findMLSName(x.idxID)
                return this.addCleanLabel(x, MLSName)
            })
        }
    },
    watch: {
        defaultPropertyTypeSelected (newVal, oldVal) {
            if (newVal === '') {
                this.$emit('form-field-update', { key: 'defaultPropertyTypeSelected', value: this.defaultPropertyTypeOptions[0].value })
            }
        }
    },
    methods: {
        addCleanLabel (item, MLSName) {
            // Adds a label with the user friendly name
            // Save original label used on the backend
            const cleanLabel = item.label
            // When an MLS becomes unapproved on an account that previously set custom fields, MLSName can sometimes be undefined... we handle it here:
            if (!MLSName) {
                return {
                    ...item,
                    label: `${item.label} - missing MLS ${item.idxID}`,
                    cleanLabel
                }
            }
            // Finds the property type the item is in
            const propType = MLSName.propertyTypes.find(x => {
                return x.value === item.mlsPtID
            })
            // Returns a new item, one that has a user friendly name
            // which is "the custom field's name - the name of the MLS it belongs to (The property type it is in)"
            return {
                ...item,
                label: `${item.label} - ` + decodeEntities(MLSName.label) + ` (${propType.label})`,
                cleanLabel
            }
        },
        removeCleanLabel (item) {
            // We added a user friendly label, lets remove it
            // Get a copy of the item and replace the user friendly label with the database label
            const updatedItem = {
                ...item,
                label: item.cleanLabel
            }
            // Delete the cleanLabel piece from the item
            delete updatedItem.cleanLabel
            // Delete the parentPtID, since that is not used in the custom fields backend
            delete updatedItem.parentPtID
            // Return the new item
            return updatedItem
        },
        cleanOptionsList (optionList) {
            return optionList.map(option => {
                option = {
                    label: decodeEntities(option.label),
                    value: option.value
                }
                return option
            })
        },
        findMLSName (idxID) {
            // Find the MLS object based on the given idxID
            return this.mlsMembership.find(option => {
                return option.value === idxID
            })
        },
        findPropertyType (pt) {
            // Find the object of the selected property type selected given
            // the property type object
            return pt.find(x => {
                return pt.selected === x.value
            })
        },
        updateCustomTags (selections, key) {
            // Get the selections ready for the backend
            // Loop through the selections and remove the cleanLabel added
            const cleanedSelections = key === 'customFieldsSelected' ? selections.map(x => {
                return this.removeCleanLabel(x)
            }) : selections
            // Emit the form update with the new clean selections
            this.$emit('form-field-update', { key, value: cleanedSelections })
        },
        decodeEntities
    },
    created () {
        this.labels = {
            cityListLabel: 'City List',
            countyListLabel: 'County List',
            postalCodeListLabel: 'Postal Code List',
            defaultPropertyTypeLabel: 'Default Property Type',
            sortOrderLabel: 'Default Sort Order',
            addressAutofillLabel: 'Address Autofill MLS'
        }
        this.defaultPropertyTypeOptions = [
            { value: 'all', label: 'All Property Types' },
            { value: 'sfr', label: 'Single Family Residential' },
            { value: 'com', label: 'Commercial' },
            { value: 'ld', label: 'Lots and Land' },
            { value: 'mfr', label: 'Multifamily Residential' },
            { value: 'rnt', label: 'Rentals' }
        ]
        this.sortOrderOptions = [
            // These are the current values used in the system, we can update them if we want to have it more
            // human readable.
            { value: 'newest', label: 'Newest Listings' },
            { value: 'oldest', label: 'Oldest Listings' },
            { value: 'pra', label: 'Least expensive to most' },
            { value: 'prd', label: 'Most expensive to least' },
            { value: 'bda', label: 'Bedrooms (Low to High)' },
            { value: 'bdd', label: 'Bedrooms (High to Low)' },
            { value: 'tba', label: 'Bathrooms (Low to High)' },
            { value: 'tbd', label: 'Bathrooms (High to Low)' },
            { value: 'sqfta', label: 'Square Feet (Low to High)' },
            { value: 'sqftd', label: 'Square Feet (High to Low)' }
        ]
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/inputTagAutocomplete.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/inputTags.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/autocomplete.scss';
.field__warning {
    color: $red;
    li {
        margin-left: 25px;
        font-weight: 700;
    }
}
</style>
