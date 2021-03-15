<template>
    <idx-block
        tag="fieldset"
        :className="templateClass"
        :formDisabled="formDisabled">
        <idx-block className="social-pro-form__settings">
            <b>General Interest Article Settings</b>
            <div>General Interest Articles come from Elevate writers and contain rich content related to homes and home life.</div>
            <idx-block className="social-pro-form__field">
                <label>Autopublish General Interest Articles</label>
                <idx-custom-select
                    ariaLabel="Select Autopublish setting"
                    :selected="autopublish"
                    :options="autopublishOptions"
                    @selected-item="$emit('form-field-update', { key: 'autopublish', value: $event.value })"
                ></idx-custom-select>
            </idx-block>
            <idx-block className="social-pro-form__field">
                <label>General Interest Article Post Day of the Week</label>
                <idx-custom-select
                    ariaLabel="Select post day"
                    :selected="postDay"
                    :options="postDayOptions"
                    @selected-item="$emit('form-field-update', { key: 'postDay', value: $event.value })"
                ></idx-custom-select>
            </idx-block>
            <idx-block className="social-pro-form__field">
                <label>General Interest Article Post Type</label>
                <idx-custom-select
                    ariaLabel="Select post type"
                    :selected="postType"
                    :options="postTypeOptions"
                    @selected-item="$emit('form-field-update', { key: 'postType', value: $event.value })"
                ></idx-custom-select>
            </idx-block>
        </idx-block>
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
    computed: {
        templateClass () {
            return this.formDisabled ? 'form-content form-content--disabled' : 'form-content'
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
.social-pro-form {
    &__field {
        margin-top: 25px;
        label {
            width: 100%;
        }
    }
}
</style>
