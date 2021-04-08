<template>
    <idx-block
        tag="fieldset"
        :className="{
            'form-content': true,
            'form-content--disabled': formDisabled
        }">
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">General Interest Article Settings</idx-block>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">Autopublish General Interest Articles</idx-form-label>
            <idx-custom-select
                ariaLabel="Select Autopublish setting"
                :disabled="formDisabled"
                :selected="autopublish"
                :options="autopublishOptions"
                @selected-item="$emit('form-field-update', { key: 'autopublish', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">General Interest Article Post Day of the Week</idx-form-label>
            <idx-custom-select
                ariaLabel="Select post day"
                :disabled="formDisabled"
                :selected="postDay"
                :options="postDayOptions"
                @selected-item="$emit('form-field-update', { key: 'postDay', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label
                :id="`${$idxStrap.prefix}article-post-type`"
                customClass="form-content__label"
            >
                General Interest Article Post Type
            </idx-form-label>
            <idx-form-input
                :id="`${$idxStrap.prefix}article-post-type__input`"
                type="text"
                :aria-labelledby="`${$idxStrap.prefix}article-post-type`"
                :disabled="formDisabled"
                :value="postType"
                @change="$emit('form-field-update',{ key: 'postType', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">General Interest Article Author</idx-form-label>
            <idx-custom-select
                ariaLabel="Select author"
                :disabled="formDisabled"
                :selected="selectedAuthor"
                :options="authors"
                @selected-item="$emit('form-field-update', { key: 'selectedAuthor', value: $event.value })"
            ></idx-custom-select>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label
                :customClass="{
                    ['form-content__label']: true,
                    ['form-content--disabled']: formDisabled
                }"
            >
                General Interest Article Categories
            </idx-form-label>
            <idx-input-tag-autocomplete
                :previousSelections="selectedCategories"
                :resultsList="categories"
                @tag-list="$emit('form-field-update', { key: 'selectedCategories', value: $event })"
            ></idx-input-tag-autocomplete>
        </idx-form-group>
    </idx-block>
</template>
<script>
export default {
    name: 'social-pro-form',
    props: {
        autopublish: {
            type: String,
            default: 'autopublish'
        },
        postDay: {
            type: String,
            default: 'sun'
        },
        postType: {
            type: String,
            default: 'post'
        },
        authors: {
            type: Array,
            default: () => []
        },
        selectedAuthor: {
            type: [String, Number],
            default: ''
        },
        categories: {
            type: Array,
            default: () => []
        },
        selectedCategories: {
            type: Array,
            default: () => []
        },
        formDisabled: {
            type: Boolean,
            default: false
        }
    },
    created () {
        this.autopublishOptions = [
            { label: 'Autopublish', value: 'autopublish' },
            { label: 'Draft', value: 'draft' }
        ]
        this.postDayOptions = [
            { label: 'Sunday', value: 'sun' },
            { label: 'Monday', value: 'mon' },
            { label: 'Tuesday', value: 'tues' },
            { label: 'Wednesday', value: 'wed' },
            { label: 'Thursday', value: 'thurs' },
            { label: 'Friday', value: 'fri' },
            { label: 'Saturday', value: 'sat' }
        ]
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/inputTagAutocomplete.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/inputTags.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/autocomplete.scss';
</style>
