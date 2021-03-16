<template>
    <idx-block className="omnibar-form form-content">
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Do you want to set up IMPress Omnibar Search?</idx-block>
            <p>A short paragraph detailing the IMPress Omnibar Search feature. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed hendrerit vulputate.</p>
        </idx-block>
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
                :options="cityListOptions"
                @selected-item="$emit('form-field-update', { key: 'cityListSelected', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">{{ labels.countyListLabel }}</idx-form-label>
            <idx-custom-select
                placeholder="Select"
                :ariaLabel="labels.countyListLabel"
                :selected="countyListSelected"
                :options="countyListOptions"
                @selected-item="$emit('form-field-update', { key: 'countyListSelected', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">{{ labels.postalCodeListLabel }}</idx-form-label>
            <idx-custom-select
                placeholder="Select"
                :ariaLabel="labels.postalCodeListLabel"
                :selected="postalCodeSelected"
                :options="postalCodeListOptions"
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
                <idx-block tag="h2" className="form-content__title">MLS Specific Property Type</idx-block>
                <p>Used or custom field searches and addresses</p>
            </idx-block>
            <idx-form-group
                v-for="(mls, key) in mlsMembership"
                :key="mls.value"
            >
                <idx-form-label customClass="form-content__label">{{ mls.label }}</idx-form-label>
                <idx-custom-select
                    :ariaLabel="mls.label"
                    :selected="mls.selected"
                    :options="mls.propertyTypes"
                    @selected-item="$emit('form-field-update-mls-membership', { key: 'mlsMembership', value: [ mls, $event.value, key, mlsMembership ] })"
                ></idx-custom-select>
            </idx-form-group>
        </idx-block>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Addresses</idx-block>
            <p>Choose which MLS is included in the address autofill. Addresses will only be included from the selected property types.</p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">{{ labels.addressAutofillLabel }}</idx-form-label>
            <idx-custom-select
                placeholder="Select MLS Source"
                :ariaLabel="labels.addressAutofillLabel"
                :selected="autofillMLSSelected"
                :options="mlsMembership"
                @selected-item="$emit('form-field-update', { key: 'autofillMLSSelected', value: $event.value })"
            ></idx-custom-select>
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
                @tag-list="updateCustomTags"
            ></idx-input-tag-autocomplete>
        </idx-form-group>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Custom Placeholder</idx-block>
            <p>This is a placeholder for the main input of Omibar Widgets.<br>
            Examples: “Search for Properties”, “Location, School, Address, or Listing ID”.</p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">Custom Placeholder</idx-form-label>
            <idx-form-input
                type="text"
                customClass=""
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
            type: String,
            default: ''
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
        }
    },
    computed: {
        invalidCustomTagsCheck () {
            const invalidTags = []
            for (const x in this.customFieldsSelectedCleaned) {
                if (this.mlsSpecificPropTypes[this.customFieldsSelectedCleaned[x].idxID] !== this.customFieldsSelectedCleaned[x].mlsPtID) {
                    invalidTags.push(this.customFieldsSelectedCleaned[x])
                }
            } return invalidTags
        },
        mlsSpecificPropTypes () {
            const selections = {}
            for (const x in this.mlsMembership) {
                selections[this.mlsMembership[x].value] = this.mlsMembership[x].selected
            }
            return selections
        },
        customFieldsOptionsCleaned () {
            const options = []
            for (let x = 0; x < this.customFieldsOptions.length; x++) {
                const MLSName = this.findMLSName(this.customFieldsOptions[x].idxID)
                this.customFieldsOptions[x].fieldNames.forEach(option => {
                    if (option.mlsPtID === this.mlsSpecificPropTypes[MLSName.value]) {
                        options.push({
                            ...this.addCleanLabel(option, MLSName),
                            idxID: MLSName.value
                        })
                    }
                })
            } return options
        },
        customFieldsSelectedCleaned () {
            return this.customFieldsSelected.map(x => {
                const MLSName = this.findMLSName(x.idxID)
                return this.addCleanLabel(x, MLSName)
            })
        }
    },
    methods: {
        addCleanLabel (item, MLSName) {
            const cleanLabel = item.label
            const propType = MLSName.propertyTypes.find(x => {
                return x.value === item.mlsPtID
            })
            return {
                ...item,
                label: `${item.label} - ${MLSName.label} (${propType.label})`,
                cleanLabel
            }
        },
        removeCleanLabel (item) {
            const updatedItem = {
                ...item,
                label: item.cleanLabel
            }
            delete updatedItem.cleanLabel
            delete updatedItem.parentPtID
            return updatedItem
        },
        findMLSName (idxID) {
            return this.mlsMembership.find(option => {
                return option.value === idxID
            })
        },
        findPropertyType (pt) {
            return pt.find(x => {
                return pt.selected === x.value
            })
        },
        updateCustomTags (selections) {
            const cleanedSelections = selections.map(x => {
                return this.removeCleanLabel(x)
            })
            this.$emit('form-field-update', { key: 'customFieldsSelected', value: cleanedSelections })
        }
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
