<template>
    <idx-block tag="fieldset" className="fieldset agents-settings form-content">
        <idx-form-group>
            <idx-block className="form-content__label"><idx-block tag="h3" className="form-content__title">CSS Settings</idx-block> Detailed sentence or two describing deregistering IMPress Agents CSS files so that the installed theme’s CSS won’t have specificity issues.</idx-block>
            <idx-block className="form-content__toggle">
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
            <idx-block className="form-content__label"><idx-block tag="h3" className="form-content__title">Default Number of Posts</idx-block> Default Number of Posts Description of the Default Number of Posts setting in IMPress Agents. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="number-of-posts">Default Number of Posts</idx-form-label>
            <idx-form-input
                type="text"
                id="number-of-posts"
                :value="numberOfPosts"
                @change="agentSettingsStateChange({ key: 'numberOfPosts', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="form-content__label"><idx-block tag="h3" className="form-content__title">Directory Slug</idx-block> Directory Slug Description of the Employee Slug setting in IMPress Agents. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="directory-slug">Directory Slug</idx-form-label>
            <idx-form-input
                type="text"
                id="directory-slug"
                :value="directorySlug"
                @change="agentSettingsStateChange({ key: 'directorySlug', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="form-content__label"><idx-block tag="h3" className="form-content__title">Custom Wrapper</idx-block> Detailed sentence or two describing how custom wrappers can be used and how to set them up properly.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="wrapper-start">Wrapper Start HTML</idx-form-label>
            <idx-form-input
                type="text"
                id="wrapper-start"
                :value="wrapperStart"
                @change="agentSettingsStateChange({ key: 'wrapperStart', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="wrapper-end">Wrapper End HTML</idx-form-label>
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
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider';
@import '../styles/formContentStyles.scss';
</style>
