<template>
    <idx-block
        tag="fieldset"
        :className="{
            'form-content': true,
            'form-content--disabled': formDisabled
        }">
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Imported Listings</idx-block>
            <p>These settings apply to any imported IDX listings. Imported listings are updated via the latest API response twice daily.</p>
        </idx-block>
        <idx-form-group>
            <idx-block tag="h3" className="form-content__label">Update Listings</idx-block>
            <idx-rich-select
                v-for="option in updateOptions"
                :key="`${option.value}-${option.label}`"
                :label="option.label"
                :description="option.description"
                :radio="false"
                :checked="updateListings === option.value"
                :disabled="formDisabled"
                @change="$emit('form-field-update', {
                    key: 'updateListings',
                    value: option.value
                })"
            ></idx-rich-select>
        </idx-form-group>
        <idx-form-group>
            <idx-block tag="h3" className="form-content__label">Sold Listings</idx-block>
            <idx-rich-select
                v-for="option in soldListingsOptions"
                :key="`${option.value}-${option.label}`"
                :label="option.label"
                :description="option.description"
                :radio="false"
                :checked="soldListings === option.value"
                :disabled="formDisabled"
                @change="$emit('form-field-update', {
                    key: 'soldListings',
                    value: option.value
                })"
            ></idx-rich-select>
        </idx-form-group>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Additional Import Options</idx-block>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">
                <idx-block tag="h3" className="form-content__title">{{ toggleLabels[0] }}</idx-block>
                <p>Description of the automatic import listings setting. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce ac purus eu ex lacinia placerat.</p>
            </idx-form-label>
            <idx-block className="idx-content-settings__toggle form-content__toggle">
                {{ toggleLabels[0] }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    :active="automaticImport"
                    :disabled="formDisabled"
                    :label="toggleLabels[0]"
                    @toggle="$emit('form-field-update', { key: 'automaticImport', value: !automaticImport })"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">{{ defaultListingTemplateLabel }}</idx-form-label>
            <idx-custom-select
                placeholder="Select a Template"
                :options="defaultListingTemplateOptions"
                :selected="defaultListingTemplateSelected"
                :ariaLabel="defaultListingTemplateLabel"
                :disabled="formDisabled"
                @selected-item="$emit('form-field-update', { key: 'defaultListingTemplateSelected', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">{{ importedListingsTemplateLabel }}</idx-form-label>
            <idx-custom-select
                placeholder="Select an Author"
                :options="importedListingsAuthorOptions"
                :selected="importedListingsAuthorSelected"
                :ariaLabel="importedListingsTemplateLabel"
                :disabled="formDisabled"
                @selected-item="$emit('form-field-update', { key: 'importedListingsAuthorSelected', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="form-content__toggle">
                {{ toggleLabels[1] }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    :active="displayIDXLink"
                    :disabled="formDisabled"
                    :label="toggleLabels[1]"
                    @toggle="$emit('form-field-update', { key: 'displayIDXLink', value: !displayIDXLink })"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="form-content__label">
                <idx-block tag="h3" :id="`${$idxStrap.prefix}importTitleLabel`" className="form-content__title">Import Title</idx-block>
                <p>By default, your imported listings will use the street address as the page title and permalink</p>
            </idx-block>
            <idx-form-input
                type="text"
                aria-labelledby="importTitleLabel"
                :id="`${$idxStrap.prefix}importTitle`"
                :disabled="formDisabled"
                :value="importTitle"
                @change="$emit('form-field-update', { key: 'importTitle', value: $event.target.value })"
            ></idx-form-input>
        </idx-form-group>
        <hr/>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Advanced Field Settings</idx-block>
        </idx-block>
        <idx-form-group>
            <idx-block className="idx-content-settings__toggle form-content__toggle">
                {{ toggleLabels[2] }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    :active="advancedFieldData"
                    :disabled="formDisabled"
                    :label="toggleLabels[2]"
                    @toggle="$emit('form-field-update', { key: 'advancedFieldData', value: !advancedFieldData })"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="idx-content-settings__toggle form-content__toggle">
                {{ toggleLabels[3] }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    :active="displayAdvancedFields"
                    :disabled="formDisabled"
                    :label="toggleLabels[3]"
                    @toggle="$emit('form-field-update', { key: 'displayAdvancedFields', value: !displayAdvancedFields })"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
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
            type: [String, Number],
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
        },
        formDisabled: {
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
            { label: 'Keep All', value: 'sold-keep', description: 'All imported listings will be kept and published with the status changed to reflect as sold' },
            { label: 'Keep as Draft', value: 'sold-draft', description: 'All imported listings will be kept as a draft with the status changed to reflect as sold' },
            { label: 'Delete Sold (Not Recommended)', value: 'sold-delete', description: 'Sold listings and attached featured images will be deleted from your WordPress database and media library' }
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
</style>
