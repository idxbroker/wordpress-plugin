<template>
    <idx-block className="idx-content-settings form-content">
        <div>
            <idx-block className="form-content__title">Imported Listings</idx-block>
            <div>These settings apply to any imported IDX listings. Imported listings are updated via the latest API response twice daily.</div>
        </div>
        <div>
            <b>Update Listings</b>
            <idx-rich-select
                v-for="option in updateOptions"
                :key="`${option.value}-${option.label}`"
                :label="option.label"
                :description="option.description"
                :radio="false"
                :checked="updateListings === option.value"
                @change="listingsSettingsStateChange({
                    key: 'updateListings',
                    value: option.value
                })"
            ></idx-rich-select>
        </div>
        <div>
            <b>Sold Listings</b>
            <idx-rich-select
                v-for="option in soldListingsOptions"
                :key="`${option.value}-${option.label}`"
                :label="option.label"
                :description="option.description"
                :radio="false"
                :checked="soldListings === option.value"
                @change="listingsSettingsStateChange({
                    key: 'soldListings',
                    value: option.value
                })"
            ></idx-rich-select>
        </div>
        <idx-block className="idx-content-settings idx-content-settings__additional-imports">
            <div>
                <idx-block className="form-content__title">Additional Import Options</idx-block>
                <b>{{ toggleLabels[0] }}</b>
                <div>Description of the automatic import listings setting. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce ac purus eu ex lacinia placerat.</div>
                <idx-block className="idx-content-settings__toggle form-content__toggle">
                    {{ toggleLabels[0] }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        @toggle="listingsSettingsStateChange({ key: 'automaticImport', value: !automaticImport })"
                        :active="automaticImport"
                        :label="toggleLabels[0]"
                    ></idx-toggle-slider>
                </idx-block>
            </div>
            <div>
                {{ defaultListingTemplateLabel }}
                <idx-custom-select
                    placeholder="Select a Template"
                    :options="defaultListingTemplateOptions"
                    :selected="defaultListingTemplateSelected"
                    :ariaLabel="defaultListingTemplateLabel"
                    @toggle="listingsSettingsStateChange({ key: 'defaultListingTemplateSelected', value: $event.value })"
                ></idx-custom-select>
            </div>
            <div>
                {{ importedListingsTemplateLabel }}
                <idx-custom-select
                    placeholder="Select an Author"
                    :options="importedListingsAuthorOptions"
                    :selected="importedListingsAuthorSelected"
                    :ariaLabel="importedListingsTemplateLabel"
                    @toggle="listingsSettingsStateChange({ key: 'importedListingsAuthorSelected', value: $event.value })"
                ></idx-custom-select>
            </div>
            <idx-block className="idx-content-settings__toggle form-content__toggle">
                {{ toggleLabels[1] }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="listingsSettingsStateChange({ key: 'displayIDXLink', value: !displayIDXLink })"
                    :active="displayIDXLink"
                    :label="toggleLabels[1]"
                ></idx-toggle-slider>
            </idx-block>
            <idx-form-group>
                <b>Import Title</b>
                <idx-block>By default, your imported listigns will use the street address as the page title and permalink</idx-block>
                <idx-form-input
                    type="text"
                    customClass="idx-content-settings__import-title"
                    :value="importTitle"
                    @change="listingsSettingsStateChange({ key: 'importTitle', value: $event.target.value })"
                ></idx-form-input>
            </idx-form-group>
        </idx-block>
        <idx-block className="idx-content-settings__advanced">
            <idx-block className="form-content__title">Advanced Field Settings</idx-block>
            <idx-block className="idx-content-settings ">
                <idx-block className="idx-content-settings__toggle form-content__toggle">
                    {{ toggleLabels[2] }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        @toggle="listingsSettingsStateChange({ key: 'advancedFieldData', value: !advancedFieldData })"
                        :active="advancedFieldData"
                        :label="toggleLabels[2]"
                    ></idx-toggle-slider>
                </idx-block>
                <idx-block className="idx-content-settings__toggle form-content__toggle">
                    {{ toggleLabels[3] }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        @toggle="listingsSettingsStateChange({ key: 'displayAdvancedFields', value: !displayAdvancedFields })"
                        :active="displayAdvancedFields"
                        :label="toggleLabels[3]"
                    ></idx-toggle-slider>
                </idx-block>
            </idx-block>
        </idx-block>
    </idx-block>
</template>
<script>
import { mapState, mapActions } from 'vuex'
export default {
    name: 'impress-listings-idx-content',
    data () {
        return {
            updateOptions: [
                { label: 'Update All', value: 'update-all', description: 'Update all imported fields, including gallery and featured image. Excludes Post Title and Post Content.' },
                { label: 'Update Excluding Images', value: 'update-excluding-images', description: 'Update all imported fields, but excluding the gallery and featured image. Excludes Post Title and Post Content.' },
                { label: 'Do Not Update (Not Recommended)', value: 'no-update', description: 'Do not update any fields. Listing will be changed to sold status if it exists in the sold data feed. Displaying inaccurate MLS data may violate your IDX agreement.' }
            ],
            soldListingsOptions: [
                { label: 'Keep All', value: 'keep-all', description: 'All imported listings will be kept and published with the status changed to reflect as sold' },
                { label: 'Keep as Draft', value: 'keep-as-draft', description: 'All imported listings will be kept as a draft with the status changed to reflect as sold' },
                { label: 'Delete Sold (Not Recommended)', value: 'delete-sold', description: 'Sold listings and attached featured images will be deleted from your WordPress database and media library' }
            ],
            toggleLabels: [
                'Automatically import new listings',
                'Display link to IDX Broker details page',
                'Import Advanced Field Data',
                'Display Advanced Fields on Single Listing Pages'
            ],
            defaultListingTemplateLabel: 'Default Single Listing Template',
            importedListingsTemplateLabel: 'Imported Listings Author'
        }
    },
    computed: {
        ...mapState({
            updateListings: state => state.listingsSettings.updateListings,
            soldListings: state => state.listingsSettings.soldListings,
            automaticImport: state => state.listingsSettings.automaticImport,
            displayIDXLink: state => state.listingsSettings.displayIDXLink,
            defaultListingTemplateSelected: state => state.listingsSettings.defaultListingTemplateSelected,
            defaultListingTemplateOptions: state => state.listingsSettings.defaultListingTemplateOptions,
            importedListingsAuthorSelected: state => state.listingsSettings.importedListingsAuthorSelected,
            importedListingsAuthorOptions: state => state.listingsSettings.importedListingsAuthorOptions,
            importTitle: state => state.listingsSettings.importTitle,
            advancedFieldData: state => state.listingsSettings.advancedFieldData,
            displayAdvancedFields: state => state.listingsSettings.displayAdvancedFields
        })
    },
    methods: {
        ...mapActions({
            listingsSettingsStateChange: 'listingsSettings/listingsSettingsStateChange'
        })
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/richSelect';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect';
@import '../styles/formContentStyles.scss';
.rich-select {
    &__label {
        width: 100%;
    }
    &--check.rich-select {
        margin-bottom: 6px;
    }
}
.idx-content-settings {
    display: flex;
    flex-direction: column;
    grid-gap: 40px;
    &__import-title.form-control {
        width: 100%;
        height: 45px;
        padding-left: 15px;
        border-color: $gray-250;
    }
    &__additional-imports {
        padding-bottom: 40px;
        border-bottom: 1px solid $gray-400;
    }
    &__advanced {
        .form-content__title {
            margin-bottom: 25px;
        }
        .idx-content-settings__toggle {
            margin-bottom: 6px;
        }
    }
}
</style>
