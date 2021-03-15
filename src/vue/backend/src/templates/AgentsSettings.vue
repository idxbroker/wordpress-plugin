<template>
    <idx-block
        tag="fieldset"
        :className="templateClass"
        :formDisabled="formDisabled">
        <idx-block className="form-content__header"><idx-block tag="h3" className="form-content__title">CSS Settings</idx-block> Detailed sentence or two describing deregistering IMPress Agents CSS files so that the installed theme’s CSS won’t have specificity issues.</idx-block>
        <idx-form-group>
            <idx-block className="form-content__toggle">
                {{ cssLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="$emit('form-field-update', { key: 'deregisterMainCss', value: !deregisterMainCss })"
                    :active="deregisterMainCss"
                    :disabled="formDisabled"
                    :label="cssLabel"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <idx-block className="form-content__header"><idx-block tag="h3" className="form-content__title">Default Number of Posts</idx-block> Default Number of Posts Description of the Default Number of Posts setting in IMPress Agents. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="number-of-posts">Default Number of Posts</idx-form-label>
            <idx-form-input
                type="text"
                :disabled="formDisabled"
                id="number-of-posts"
                :value="numberOfPosts"
                @change="$emit('form-field-update', { key: 'numberOfPosts', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-block className="form-content__header"><idx-block tag="h3" className="form-content__title">Directory Slug</idx-block> Directory Slug Description of the Employee Slug setting in IMPress Agents. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="directory-slug">Directory Slug</idx-form-label>
            <idx-form-input
                type="text"
                :disabled="formDisabled"
                id="directory-slug"
                :value="directorySlug"
                @change="$emit('form-field-update', { key: 'directorySlug', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-block className="form-content__header"><idx-block tag="h3" className="form-content__title">Custom Wrapper</idx-block> Detailed sentence or two describing how custom wrappers can be used and how to set them up properly.</idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="wrapper-start">Wrapper Start HTML</idx-form-label>
            <idx-form-input
                type="text"
                :disabled="formDisabled"
                id="wrapper-start"
                :value="wrapperStart"
                @change="$emit('form-field-update', { key: 'wrapperStart', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="wrapper-end">Wrapper End HTML</idx-form-label>
            <idx-form-input
                type="text"
                :disabled="formDisabled"
                id="wrapper-end"
                :value="wrapperEnd"
                @change="$emit('form-field-update', { key: 'wrapperEnd', value: $event.target.value })"
            />
        </idx-form-group>
    </idx-block>
</template>
<script>
export default {
    name: 'agentSettings',
    props: {
        deregisterMainCss: {
            type: Boolean,
            default: false
        },
        numberOfPosts: {
            type: [String, Number],
            default: ''
        },
        directorySlug: {
            type: String,
            default: 'employees'
        },
        wrapperStart: {
            type: String,
            default: ''
        },
        wrapperEnd: {
            type: String,
            default: ''
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
        this.cssLabel = 'Deregister IMPress Agents Main CSS?'
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider';
</style>
