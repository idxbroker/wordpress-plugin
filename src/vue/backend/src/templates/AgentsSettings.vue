<template>
    <idx-block tag="fieldset" className="fieldset agents-settings">
        <idx-form-group>
            <idx-block className="control-label"><strong>CSS Settings</strong> Detailed sentence or two describing deregistering IMPress Agents CSS files so that the installed theme’s CSS won’t have specificity issues.</idx-block>
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
            <idx-form-label customClass="control-label" for="website-wrapper"><strong>Default Number of Posts</strong> Default Number of Posts Description of the Default Number of Posts setting in IMPress Agents. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-form-label>
            <idx-form-input
                type="text"
                id="website-wrapper"
                :value="numberOfPosts"
                @change="agentSettingsStateChange({ key: numberOfPosts, value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="control-label" for="website-wrapper"><strong>Directory Slug</strong> Directory Slug Description of the Employee Slug setting in IMPress Agents. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-form-label>
            <idx-form-input
                type="text"
                id="website-wrapper"
                :value="directorySlug"
                @change="agentSettingsStateChange({ key: directorySlug, value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-label"><strong>Custom Wrapper</strong> Detailed sentence or two describing how custom wrappers can be used and how to set them up properly.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="control-label" for="website-wrapper">Wrapper Start HTML</idx-form-label>
            <idx-form-input
                type="text"
                id="website-wrapper"
                :value="wrapperStart"
                @change="agentSettingsStateChange({ key: wrapperStart, value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="control-label" for="website-wrapper">Wrapper End HTML</idx-form-label>
            <idx-form-input
                type="text"
                id="website-wrapper"
                :value="wrapperEnd"
                @change="agentSettingsStateChange({ key: wrapperEnd, value: $event.target.value })"
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
    input[type=color], input[type=date], input[type=datetime-local], input[type=datetime], input[type=email], input[type=month], input[type=number], input[type=password], input[type=search], input[type=tel], input[type=text], input[type=time], input[type=url], input[type=week], select, textarea {
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
