<template>
    <idx-block className="idx-content-settings form-content">
        <div>
            <idx-block tag="h2" className="form-content__title">Imported Listings</idx-block>
            <p>These settings apply to any imported IDX listings. Imported listings are updated via the latest API response twice daily.</p>
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
                @change="$emit('form-field-update', {
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
                @change="$emit('form-field-update', {
                    key: 'soldListings',
                    value: option.value
                })"
            ></idx-rich-select>
        </div>
        <idx-block className="idx-content-settings idx-content-settings__additional-imports">
            <div>
                <idx-block tag="h2" className="form-content__title">Additional Import Options</idx-block>
                <b>{{ toggleLabels[0] }}</b>
                <div>Description of the automatic import listings setting. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce ac purus eu ex lacinia placerat.</div>
                <idx-block className="idx-content-settings__toggle form-content__toggle">
                    {{ toggleLabels[0] }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        @toggle="$emit('form-field-update', { key: 'automaticImport', value: !automaticImport })"
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
                    @toggle="$emit('form-field-update', { key: 'defaultListingTemplateSelected', value: $event.value })"
                ></idx-custom-select>
            </div>
            <div>
                {{ importedListingsTemplateLabel }}
                <idx-custom-select
                    placeholder="Select an Author"
                    :options="importedListingsAuthorOptions"
                    :selected="importedListingsAuthorSelected"
                    :ariaLabel="importedListingsTemplateLabel"
                    @toggle="$emit('form-field-update', { key: 'importedListingsAuthorSelected', value: $event.value })"
                ></idx-custom-select>
            </div>
            <idx-block className="idx-content-settings__toggle form-content__toggle">
                {{ toggleLabels[1] }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="$emit('form-field-update', { key: 'displayIDXLink', value: !displayIDXLink })"
                    :active="displayIDXLink"
                    :label="toggleLabels[1]"
                ></idx-toggle-slider>
            </idx-block>
            <idx-form-group>
                <b>Import Title</b>
                <idx-block>By default, your imported listings will use the street address as the page title and permalink</idx-block>
                <idx-form-input
                    type="text"
                    customClass="idx-content-settings__import-title"
                    :value="importTitle"
                    @change="$emit('form-field-update', { key: 'importTitle', value: $event.target.value })"
                ></idx-form-input>
            </idx-form-group>
        </idx-block>
        <idx-block className="idx-content-settings__advanced">
            <idx-block tag="h2" className="form-content__title">Advanced Field Settings</idx-block>
            <idx-block className="idx-content-settings ">
                <idx-block className="idx-content-settings__toggle form-content__toggle">
                    {{ toggleLabels[2] }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        @toggle="$emit('form-field-update', { key: 'advancedFieldData', value: !advancedFieldData })"
                        :active="advancedFieldData"
                        :label="toggleLabels[2]"
                    ></idx-toggle-slider>
                </idx-block>
                <idx-block className="idx-content-settings__toggle form-content__toggle">
                    {{ toggleLabels[3] }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        @toggle="$emit('form-field-update', { key: 'displayAdvancedFields', value: !displayAdvancedFields })"
                        :active="displayAdvancedFields"
                        :label="toggleLabels[3]"
                    ></idx-toggle-slider>
                </idx-block>
            </idx-block>
        </idx-block>
    </idx-block>
</template>
<script>
export default {
    name: 'impress-listings-idx-content',
    inheritAttrs: false,
    props: {
        updateListings: {
            type: String,
            default: 'update-all'
        },
        soldListings: {
            type: String,
            default: 'keep-all'
        },
        automaticImport: {
            type: Boolean,
            default: false
        },
        displayIDXLink: {
            type: Boolean,
            default: false
        },
        defaultListingTemplateSelected: {
            type: String,
            default: ''
        },
        defaultListingTemplateOptions: {
            type: Array,
            default: () => []
        },
        importedListingsAuthorSelected: {
            type: String,
            default: ''
        },
        importedListingsAuthorOptions: {
            type: Array,
            default: () => []
        },
        importTitle: {
            type: String,
            default: '{{address}}'
        },
        advancedFieldData: {
            type: Boolean,
            default: false
        },
        displayAdvancedFields: {
            type: Boolean,
            default: false
        }
    },
    created () {
        this.updateOptions = [
            { label: 'Update All', value: 'update-all', description: 'Update all imported fields, including gallery and featured image. Excludes Post Title and Post Content.' },
            { label: 'Update Excluding Images', value: 'update-excluding-images', description: 'Update all imported fields, but excluding the gallery and featured image. Excludes Post Title and Post Content.' },
            { label: 'Do Not Update (Not Recommended)', value: 'no-update', description: 'Do not update any fields. Listing will be changed to sold status if it exists in the sold data feed. Displaying inaccurate MLS data may violate your IDX agreement.' }
        ]
        this.soldListingsOptions = [
            { label: 'Keep All', value: 'keep-all', description: 'All imported listings will be kept and published with the status changed to reflect as sold' },
            { label: 'Keep as Draft', value: 'keep-as-draft', description: 'All imported listings will be kept as a draft with the status changed to reflect as sold' },
            { label: 'Delete Sold (Not Recommended)', value: 'delete-sold', description: 'Sold listings and attached featured images will be deleted from your WordPress database and media library' }
        ]
        this.toggleLabels = [
            'Automatically import new listings',
            'Display link to IDX Broker details page',
            'Import Advanced Field Data',
            'Display Advanced Fields on Single Listing Pages'
        ]
        this.defaultListingTemplateLabel = 'Default Single Listing Template'
        this.importedListingsTemplateLabel = 'Imported Listings Author'
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/richSelect';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect';
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
