<template>
    <idx-block
        tag="fieldset"
        :className="{
            'form-content': true,
            'form-content--disabled': formDisabled
        }">
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">CSS Settings</idx-block>
            <p>Here you can deregister the IMPress Agents CSS files and move to your theme's css file for ease of customization.</p>
        </idx-block>
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
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Default Number of Posts</idx-block>
            <p>
                The default number of posts displayed on a employee archive page is 9. Here you can set a custom number. Enter -1 to display all employee posts.
                <i>If you have more than 20-30 posts, it's not recommended to show all or your page will load slow.</i>
            </p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}number-of-posts`">Default Number of Posts</idx-form-label>
            <idx-form-input
                type="text"
                :disabled="formDisabled"
                :id="`${$idxStrap.prefix}number-of-posts`"
                :value="numberOfPosts"
                @change="$emit('form-field-update', { key: 'numberOfPosts', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Directory Slug</idx-block>
            <p>
                Optionally change the slug of the employee post type.
                Don't forget to
                <a href="../wp-admin/options-permalink.php" target="_blank">
                    reset your permalinks
                </a>
                if you change the slug!
            </p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}directory-slug`">Directory Slug</idx-form-label>
            <idx-form-input
                type="text"
                :disabled="formDisabled"
                :id="`${$idxStrap.prefix}directory-slug`"
                :value="directorySlug"
                @change="$emit('form-field-update', { key: 'directorySlug', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Custom Wrapper</idx-block>
            <p>If your theme's content HTML ID's and Classes are different than the included template, you can enter the HTML of your content wrapper beginning and end.</p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}wrapper-start`">Wrapper Start HTML</idx-form-label>
            <idx-form-input
                type="text"
                :disabled="formDisabled"
                :id="`${$idxStrap.prefix}wrapper-start`"
                :value="wrapperStart"
                @change="$emit('form-field-update', { key: 'wrapperStart', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}wrapper-end`">Wrapper End HTML</idx-form-label>
            <idx-form-input
                type="text"
                :disabled="formDisabled"
                :id="`${$idxStrap.prefix}wrapper-end`"
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
    created () {
        this.cssLabel = 'Deregister IMPress Agents Main CSS?'
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider';
</style>
