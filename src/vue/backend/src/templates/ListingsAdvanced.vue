<template>
    <idx-block tag="fieldset" className="fieldset listings-advanced">
        <idx-form-group>
            <idx-block className="control-label"><strong>CSS Settings</strong> Detailed sentence or two describing deregistering IMPress Listing CSS files so that the installed theme’s CSS won’t have specificity issues.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-toggle-slider">
                {{ mainCssLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="listingsSettingsStateChange({ key: 'deregisterMainCss', value: !deregisterMainCss })"
                    :active="deregisterMainCss"
                    :label="mainCssLabel"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-toggle-slider">
                {{ widgetsCssLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="listingsSettingsStateChange({ key: 'deregisterWidgetCss', value: !deregisterWidgetCss })"
                    :active="deregisterWidgetCss"
                    :label="widgetsCssLabel"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <hr/>
        <idx-form-group>
            <idx-block className="control-label"><strong>Form Submissions to IDX Broker</strong> Send all contact form submissions to IDX Broker as a lead.<br> <b>Note:</b> This option only works while using default contact forms.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-toggle-slider">
                {{ sendFormLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="listingsSettingsStateChange({ key: 'sendFormSubmission', value: !sendFormSubmission })"
                    :active="sendFormSubmission"
                    :label="sendFormLabel"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-label"><strong>Default Form Shortcode</strong> Detailed sentence or two describing short code compatibility with Contact Form plugin. If no short code is entered the template uses default contact forms.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="control-label" for="form-shortcode">Form Shortcode</idx-form-label>
            <idx-form-input
                type="text"
                id="form-shortcode"
                :value="formShortcode"
                @change="listingsSettingsStateChange({ key: 'formShortcode', value: $event.target.value })"
            />
        </idx-form-group>
        <hr/>
        <idx-form-group>
            <idx-block className="control-label"><strong>Google Maps</strong> Listings can be automatically mapped if a latitude and longitude is provided. A Google Maps API Key is required - <a href="https://developers.google.com/maps/documentation/javascript/get-api-key">click here</a> to register.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="control-label" for="google-maps">Google Maps API Key</idx-form-label>
            <idx-form-input
                type="text"
                id="google-maps"
                :value="googleMapsAPIKey"
                @change="listingsSettingsStateChange({ key: 'googleMapsAPIKey', value: $event.target.value })"
            />
        </idx-form-group>
        <hr/>
        <idx-form-group>
            <idx-block className="control-label"><strong>Custom Wrapper</strong> Detailed sentence or two describing how custom wrappers can be used and how to set them up properly.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="control-label" for="wrapper-start-html">Wrapper Start HTML</idx-form-label>
            <idx-form-input
                type="text"
                id="wrapper-start-html"
                :value="wrapperStart"
                @change="listingsSettingsStateChange({ key: 'wrapperStart', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="control-label" for="wrapper-end-html">Wrapper End HTML</idx-form-label>
            <idx-form-input
                type="text"
                id="wrapper-end-html"
                :value="wrapperEnd"
                @change="listingsSettingsStateChange({ key: 'wrapperEnd', value: $event.target.value })"
            />
        </idx-form-group>
        <hr/>
        <idx-form-group>
            <idx-block className="control-label"><strong>Plugin Uninstallation</strong> Checking this option will delete <b>all</b> plugin data when uninstalling the plugin.</idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-toggle-slider">
                {{ pluginUninstallationLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="listingsSettingsStateChange({ key: 'sendFormSubmission', value: !sendFormSubmission })"
                    :active="sendFormSubmission"
                    :label="pluginUninstallationLabel"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
    </idx-block>
</template>
<script>
import { mapState, mapActions } from 'vuex'
export default {
    name: 'ListingsAdvanced',
    data () {
        return {
            mainCssLabel: 'Deregister IMPress Listings Main CSS?',
            widgetsCssLabel: 'Deregister IMPress Listings Widgets CSS?',
            sendFormLabel: 'Send Form Submissions to IDX Broker',
            pluginUninstallationLabel: 'Delete Plugin Data on Uninstall'
        }
    },
    computed: {
        ...mapState({
            deregisterMainCss: state => state.listingsSettings.deregisterMainCss,
            deregisterWidgetCss: state => state.listingsSettings.deregisterWidgetCss,
            sendFormSubmission: state => state.listingsSettings.sendFormSubmission,
            formShortcode: state => state.listingsSettings.formShortcode,
            googleMapsAPIKey: state => state.listingsSettings.googleMapsAPIKey,
            wrapperStart: state => state.listingsSettings.wrapperStart,
            wrapperEnd: state => state.listingsSettings.wrapperEnd,
            deletePluginDataOnUninstall: state => state.listingsSettings.deletePluginDataOnUninstall
        })
    },
    methods: {
        ...mapActions({
            listingsSettingsStateChange: 'listingsSettings/listingsSettingsStateChange'
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
    --space-6: 24px;
    --font-size-label: 16px;
    --font-size-p: 16px;
    --line-height-label: 22px;
    --line-height-p: 28px;
    font-size: var(--font-size-p);
    line-height: var(--line-height-p);
    hr {
        border: 1px solid $gray-400;
        margin-bottom: var(--space-6);
        margin-top: var(--space-6);
        opacity: 1;
    }
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
