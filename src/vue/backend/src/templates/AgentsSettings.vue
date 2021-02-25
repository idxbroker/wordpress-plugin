<template>
    <idx-block tag="fieldset" className="fieldset agents-settings">
        <idx-form-group>
            <idx-block className="control-label"><strong>CSS Settings</strong> Detailed sentence or two describing deregistering IMPress Agents CSS files so that the installed theme’s CSS won’t have specificity issues.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-toggle-slider">
                {{ cssLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="agentSettingsStateChange({ key: 'deregisterMainCss', value: !deregisterMainCss })"
                    :active="deregisterMainCss"
                    :label="cssLabel"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-label"><strong>Default Number of Posts</strong> Default Number of Posts Description of the Default Number of Posts setting in IMPress Agents. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="control-label" for="number-of-posts">Default Number of Posts</idx-form-label>
            <idx-form-input
                type="text"
                id="number-of-posts"
                :value="numberOfPosts"
                @change="agentSettingsStateChange({ key: 'numberOfPosts', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-label"><strong>Directory Slug</strong> Directory Slug Description of the Employee Slug setting in IMPress Agents. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="control-label" for="directory-slug">Directory Slug</idx-form-label>
            <idx-form-input
                type="text"
                id="directory-slug"
                :value="directorySlug"
                @change="agentSettingsStateChange({ key: 'directorySlug', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-label"><strong>Custom Wrapper</strong> Detailed sentence or two describing how custom wrappers can be used and how to set them up properly.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="control-label" for="wrapper-start">Wrapper Start HTML</idx-form-label>
            <idx-form-input
                type="text"
                id="wrapper-start"
                :value="wrapperStart"
                @change="agentSettingsStateChange({ key: 'wrapperStart', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="control-label" for="wrapper-end">Wrapper End HTML</idx-form-label>
            <idx-form-input
                type="text"
                id="wrapper-end"
                :value="wrapperEnd"
                @change="agentSettingsStateChange({ key: 'wrapperEnd', value: $event.target.value })"
            />
        </idx-form-group>
    </idx-block>
</template>
<script>
import { mapState, mapActions } from 'vuex'
export default {
    name: 'agentSettings',
    data () {
        return {
            cssLabel: 'Deregister IMPress Agents Main CSS?'
        }
    },
    computed: {
        ...mapState({
            deregisterMainCss: state => state.agentSettings.deregisterMainCss,
            numberOfPosts: state => state.agentSettings.numberOfPosts,
            directorySlug: state => state.agentSettings.directorySlug,
            wrapperStart: state => state.agentSettings.wrapperStart,
            wrapperEnd: state => state.agentSettings.wrapperEnd
        })
    },
    methods: {
        ...mapActions({
            agentSettingsStateChange: 'agentSettings/agentSettingsStateChange'
        })
    }
}
</script>
<style lang="scss">
@import '~bootstrap/scss/forms';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider.scss';
.fieldset {
    // Global Styles
    --space-1: 4px;
    --space-2: 8px;
    --space-3: 12px;
    --space-4: 16px;
    --space-5: 20px;
    --space-6: 24px;
    --font-size-label: 16px;
    --font-size-p: 16px;
    --line-height-label: 22px;
    --line-height-p: 28px;
    font-size: var(--font-size-p);
    line-height: var(--line-height-p);
    input[type=text] {
        border: 1px solid $gray-250;
        color: $gray-875;
        line-height: 1.5;
        padding: 0.625rem 1.25rem;
    }
    .form-group {
        margin-bottom: var(--space-6);
    }
    .control-label {
        display: block;
        font-size: var(--font-size-label);
        line-height: var(--line-height-label);
        margin-bottom: var(--space-2);
        width: auto;
        strong {
            display: block;
            margin-bottom: var(--space-1);
        }
    }
    .control-toggle-slider {
        align-items: center;
        background-color: $gray-150;
        display: flex;
        justify-content: space-between;
        margin-top: var(--space-4);
        padding: var(--space-3) var(--space-5);
    }
}
</style>
