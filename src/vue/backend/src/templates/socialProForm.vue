<template>
    <idx-block
        tag="fieldset"
        :className="{
            'form-content': true,
            'form-content--disabled': formDisabled
        }">
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">General Interest Article Settings</idx-block>
            <p>General Interest Articles come from Elevate writers and contain rich content related to homes and home life.</p>
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
            <idx-form-label customClass="form-content__label">General Interest Article Post Type</idx-form-label>
            <idx-custom-select
                ariaLabel="Select post type"
                :disabled="formDisabled"
                :selected="postType"
                :options="postTypeOptions"
                @selected-item="$emit('form-field-update', { key: 'postType', value: $event.value })"
            ></idx-custom-select>
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
        this.postTypeOptions = [
            { label: 'Post', value: 'post' }
        ]
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect.scss';
</style>
