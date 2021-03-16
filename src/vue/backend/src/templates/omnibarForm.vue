<template>
    <idx-block
        tag="fieldset"
        :className="{
            'form-content': true,
            'form-content--disabled': formDisabled
        }">
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
                :ariaLabel="labels.postalCodeListLabel"
                placeholder="Select"
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
                :ariaLabel="labels.defaultPropertyTypeLabel"
                placeholder="Choose the property type for default and custom fields"
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
                :key="mls.name"
            >
                <idx-form-label customClass="form-content__label">{{ mls.name }}</idx-form-label>
                <idx-custom-select
                    :ariaLabel="mls.name"
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
                :ariaLabel="labels.addressAutofillLabel"
                placeholder="Select MLS Source"
                :selected="autofillMLS"
                :options="mlsNames"
                @selected-item="$emit('form-field-update', { key: 'autofillMLS', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Custom Fields</idx-block>
            <p>By default the omnibar searches by City, County, Postal Code, or Listing ID. Add up to 10 custom fields to be used as well.</p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">Add Custom Fields</idx-form-label>
            <idx-input-tag-autocomplete
                placeholder="Enter List Item"
                :limit="10"
                :previousSelections="customFieldsSelected"
                :resultsList="customFieldsOptions"
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
        autofillMLS: {
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
        },
        formDisabled: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        mlsNames () {
            return this.mlsMembership.map(x => {
                return { value: x.value, label: x.name }
            })
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
</style>
