<template>
    <idx-block className="omnibar-form form-content">
        <idx-block className="omnibar-form__description">
            <idx-block className="omnibar-form__title">IMPress Omnibar Search</idx-block>
            <div>
                <b>Do you want to set up IMPress Omnibar Search?</b>
                <br>
                A short paragraph detailing the IMPress Omnibar Search feature. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed hendrerit vulputate.
            </div>
        </idx-block>
        <div>
            <idx-block className="omnibar-form__ccz omnibar-form__field-subset">
                <div>
                    <b>City, County, and Postal Code Lists</b>
                    <br>
                    Only locations in these lists will return results.
                </div>
                <idx-block className="omnibar-form__field">
                    <div>{{ labels.cityListLabel }}</div>
                    <idx-custom-select
                        placeholder="Select"
                        :ariaLabel="labels.cityListLabel"
                        :selected="cityListSelected"
                        :options="cityListOptions"
                        @selected-item="omnibarStateChange({ key: 'cityListSelected', value: $event.value })"
                    ></idx-custom-select>
                </idx-block>
                    <idx-block className="omnibar-form__field">
                    <div>{{ labels.countyListLabel }}</div>
                    <idx-custom-select
                        placeholder="Select"
                        :ariaLabel="labels.countyListLabel"
                        :selected="countyListSelected"
                        :options="countyListOptions"
                        @selected-item="omnibarStateChange({ key: 'countyListSelected', value: $event.value })"
                    ></idx-custom-select>
                </idx-block>
                <idx-block className="omnibar-form__field">
                    <div>{{ labels.postalCodeListLabel }}</div>
                    <idx-custom-select
                        :ariaLabel="labels.postalCodeListLabel"
                        placeholder="Select"
                        :selected="postalCodeSelected"
                        :options="postalCodeListOptions"
                        @selected-item="omnibarStateChange({ key: 'postalCodeSelected', value: $event.value })"
                    ></idx-custom-select>
                </idx-block>
            </idx-block>
            <idx-block className="omnibar-form__field">
                <b>Property Type</b>
                <idx-block className="omnibar-form__field-description">
                    Choose the property type for default and custom fields
                </idx-block>
                <div>{{ labels.defaultPropertyTypeLabel }}</div>
                <idx-custom-select
                    :ariaLabel="labels.defaultPropertyTypeLabel"
                    placeholder="Choose the property type for default and custom fields"
                    :selected="defaultPropertyTypeSelected"
                    :options="defaultPropertyTypeOptions"
                    @selected-item="omnibarStateChange({ key: 'defaultPropertyTypeSelected', value: $event.value })"
                ></idx-custom-select>
            </idx-block>
        </div>
        <idx-block className="form-content">
            <idx-block className="omnibar-form__advanced-settings form-content__toggle">
                <div>{{ labels.advancedSettingsLabel }}</div>
                <idx-toggle-slider
                    uncheckedState="Off"
                    checkedState="On"
                    @toggle="showAdvanced = !showAdvanced"
                    :active="showAdvanced"
                    :label="labels.advancedSettingsLabel"
                ></idx-toggle-slider>
            </idx-block>
            <idx-block v-if="showAdvanced" className="form-content">
                <idx-block className="omnibar-form__field-subset">
                    <div>
                        <b>MLS Specific Property Type</b>
                        <br>
                        Used or custom field searches and addresses
                    </div>
                    <idx-block
                        className="omnibar-form__field"
                        v-for="(mls, key) in mlsMembership"
                        :key="mls.name"
                    >
                        <div>{{ mls.name }}</div>
                        <idx-custom-select
                            :ariaLabel="mls.name"
                            :selected="mls.selected"
                            :options="mls.propertyTypes"
                            @selected-item="omnibarMLSStateChange({ key: 'mlsMembership', value: [ mls, $event.value, key, mlsMembership ] })"
                        ></idx-custom-select>
                    </idx-block>
                </idx-block>
                <idx-block className="omnibar-form__field omnibar-form__autofill">
                    <idx-block className="omnibar-form__field-description">
                        <b>Addresses</b>
                        <br>
                        Choose which MLS is included in the address autofill. Addresses will only be included from the selected property types.
                    </idx-block>
                    <div>
                        <div>{{ labels.addressAutofillLabel }}</div>
                        <idx-custom-select
                            :ariaLabel="labels.addressAutofillLabel"
                            placeholder="Select MLS Source"
                            :selected="autofillMLS"
                            :options="mlsNames"
                            @selected-item="omnibarStateChange({ key: 'autofillMLS', value: $event.value })"
                        ></idx-custom-select>
                    </div>
                </idx-block>
                <idx-block>
                    <idx-block className="omnibar-form__field-description">
                        <b>Custom Fields</b>
                        <br>
                        By default the omnibar searches by City, County, Postal Code, or Listing ID. Add up to 10 custom fields to be used as well.
                    </idx-block>
                    <idx-block className="form-control__label">Add Custom Fields</idx-block>
                    <idx-input-tag-autocomplete
                        placeholder="Enter List Item"
                        :limit="10"
                        :previousSelections="customFieldsSelected"
                        :resultsList="customFieldsOptions"
                    ></idx-input-tag-autocomplete>
                </idx-block>
                <idx-form-group>
                    <b>Custom Placeholder</b>
                    <idx-block className="omnibar-form__field-description">
                        This is a placeholder for the main input of Omibar Widgets.
                        <br>
                        Examples: “Search for Properties”, “Location, School, Address, or Listing ID”
                    </idx-block>
                    <idx-block className="form-content__label">Custom Placeholder</idx-block>
                    <idx-form-input
                        type="text"
                        customClass=""
                        :value="customPlaceholder"
                        @change="omnibarStateChange({ key: 'customPlaceholder', value: $event.target.value })"
                    ></idx-form-input>
                </idx-form-group>
                <idx-block className="omnibar-form__field">
                    <div>
                        <b>{{ labels.sortOrderLabel }}</b>
                        <br>
                        The default sort order for results pages
                    </div>
                    <idx-custom-select
                        :ariaLabel="labels.sortOrderLabel"
                        :selected="defaultSortOrderSelected"
                        :options="sortOrderOptions"
                        @selected-item="omnibarStateChange({ key: 'defaultSortOrderSelected', value: $event.value })"
                    ></idx-custom-select>
                </idx-block>
            </idx-block>
        </idx-block>
    </idx-block>
</template>
<script>
import { mapActions, mapState } from 'vuex'
export default {
    name: 'omnibar-form',
    data () {
        return {
            labels: {
                cityListLabel: 'City List',
                countyListLabel: 'County List',
                postalCodeListLabel: 'Postal Code List',
                defaultPropertyTypeLabel: 'Default Property Type',
                advancedSettingsLabel: 'Show Advanced Settings',
                sortOrderLabel: 'Default Sort Order',
                addressAutofillLabel: 'Address Autofill MLS'
            },
            showAdvanced: true,
            sortOrderOptions: [
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
    },
    computed: {
        ...mapState({
            cityListOptions: state => state.omnibar.cityListOptions,
            cityListSelected: state => state.omnibar.cityListSelected,
            countyListOptions: state => state.omnibar.countyListOptions,
            countyListSelected: state => state.omnibar.countyListSelected,
            postalCodeListOptions: state => state.omnibar.postalCodeListOptions,
            postalCodeSelected: state => state.omnibar.postalCodeSelected,
            defaultPropertyTypeOptions: state => state.omnibar.defaultPropertyTypeOptions,
            defaultPropertyTypeSelected: state => state.omnibar.defaultPropertyTypeSelected,
            mlsMembership: state => state.omnibar.mlsMembership,
            autofillMLS: state => state.omnibar.autofillMLS,
            customFieldsSelected: state => state.omnibar.customFieldsSelected,
            customFieldsOptions: state => state.omnibar.customFieldsOptions,
            customPlaceholder: state => state.omnibar.customPlaceholder,
            defaultSortOrderSelected: state => state.omnibar.defaultSortOrderSelected
        }),
        mlsNames () {
            return this.mlsMembership.map(x => {
                return { value: x.value, label: x.name }
            })
        }
    },
    methods: {
        ...mapActions({
            omnibarStateChange: 'omnibar/omnibarStateChange',
            omnibarMLSStateChange: 'omnibar/omnibarMLSStateChange'
        })
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
@import '~bootstrap/scss/forms';
@import '../styles/formContentStyles.scss';

.omnibar-form {
    &__description,
    &__ccz {
        border-bottom: 1px solid $gray-150;
        padding-bottom: 15px;
    }
    &__ccz {
        margin-bottom: 25px;
    }
    &__title {
        margin-bottom: 15px;
        color: $gray-800;
        font-size: 1.5rem;
        font-weight: 100;
    }
    &__advanced-settings > div {
        text-transform: uppercase;
        letter-spacing: 1.6px;
    }
    &__field-subset {
        display: flex;
        flex-direction: column;
        grid-gap: 15px;
    }
    &__field-description {
        margin-bottom: 25px;
    }
    .autocomplete {
        &__results {
            margin-top: 43px;
        }
    }
}
</style>
