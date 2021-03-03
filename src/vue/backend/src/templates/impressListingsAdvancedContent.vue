<template>
    <idx-block tag="fieldset" className="fieldset listings-advanced form-content">
        <idx-block className="control-label form-content__label">
            <idx-block className="form-content__title">CSS Settings</idx-block>
            Detailed sentence or two describing deregistering IMPress Listing CSS files so that the installed theme’s CSS won’t have specificity issues.
        </idx-block>
        <idx-block className="listings-advanced__border">
            <idx-form-group>
                <idx-block className="form-content__toggle">
                    {{ mainCssLabel }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        @toggle="setItem({ key: 'deregisterMainCss', value: !deregisterMainCss })"
                        :active="deregisterMainCss"
                        :label="mainCssLabel"
                    ></idx-toggle-slider>
                </idx-block>
            </idx-form-group>
            <idx-form-group>
                <idx-block className="form-content__toggle">
                    {{ widgetsCssLabel }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        @toggle="setItem({ key: 'deregisterWidgetCss', value: !deregisterWidgetCss })"
                        :active="deregisterWidgetCss"
                        :label="widgetsCssLabel"
                    ></idx-toggle-slider>
                </idx-block>
            </idx-form-group>
        </idx-block>
        <idx-block className="listings-advanced__border">
            <idx-block className="control-label form-content__label">
                <idx-block tag="strong" className="control-label-title">Form Submissions to IDX Broker</idx-block>
                Send all contact form submissions to IDX Broker as a lead.<br> <b>Note:</b> This option only works while using default contact forms.
            </idx-block>
            <idx-form-group>
                <idx-block className="form-content__toggle">
                    {{ sendFormLabel }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        @toggle="setItem({ key: 'sendFormSubmission', value: !sendFormSubmission })"
                        :active="sendFormSubmission"
                        :label="sendFormLabel"
                    ></idx-toggle-slider>
                </idx-block>
            </idx-form-group>
            <idx-block className="control-label form-content__label">
                <idx-block tag="strong" className="control-label-title">Default Form Shortcode</idx-block>
                Detailed sentence or two describing short code compatibility with Contact Form plugin. If no short code is entered the template uses default contact forms.
            </idx-block>
            <idx-form-group>
                <idx-form-label customClass="control-label form-content__label" for="form-shortcode">Form Shortcode</idx-form-label>
                <idx-form-input
                    type="text"
                    id="form-shortcode"
                    :value="formShortcode"
                    @change="setItem({ key: 'formShortcode', value: $event.target.value })"
                />
            </idx-form-group>
        </idx-block>
        <idx-block className="listings-advanced__border">
            <idx-block className="control-label form-content__label">
                <idx-block tag="strong" className="control-label-title">Google Maps</idx-block>
                Listings can be automatically mapped if a latitude and longitude is provided. A Google Maps API Key is required -
                <a target="_blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key">click here</a> to register.
            </idx-block>
            <idx-form-group>
                <idx-form-label customClass="control-label form-content__label" for="google-maps">Google Maps API Key</idx-form-label>
                <idx-form-input
                    type="text"
                    id="google-maps"
                    :value="googleMapsAPIKey"
                    @change="setItem({ key: 'googleMapsAPIKey', value: $event.target.value })"
                />
            </idx-form-group>
        </idx-block>
        <idx-block className="listings-advanced__border">
            <idx-block className="control-label form-content__label">
                <idx-block tag="strong" className="control-label-title">Custom Wrapper</idx-block>
                Detailed sentence or two describing how custom wrappers can be used and how to set them up properly.
            </idx-block>
            <idx-form-group>
                <idx-form-label customClass="control-label form-content__label" for="wrapper-start-html">Wrapper Start HTML</idx-form-label>
                <idx-form-input
                    type="text"
                    id="wrapper-start-html"
                    :value="wrapperStart"
                    @change="setItem({ key: 'wrapperStart', value: $event.target.value })"
                />
            </idx-form-group>
            <idx-form-group>
                <idx-form-label customClass="control-label form-content__label" for="wrapper-end-html">Wrapper End HTML</idx-form-label>
                <idx-form-input
                    type="text"
                    id="wrapper-end-html"
                    :value="wrapperEnd"
                    @change="setItem({ key: 'wrapperEnd', value: $event.target.value })"
                />
            </idx-form-group>
        </idx-block>
        <div>
            <idx-block className="control-label form-content__label">
                <idx-block tag="strong" className="control-label-title">Plugin Uninstallation</idx-block>
                Checking this option will delete <b>all</b> plugin data when uninstalling the plugin.
            </idx-block>
            <idx-form-group>
                <idx-block className="form-content__toggle">
                    {{ pluginUninstallationLabel }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        @toggle="setItem({ key: 'sendFormSubmission', value: !sendFormSubmission })"
                        :active="sendFormSubmission"
                        :label="pluginUninstallationLabel"
                    ></idx-toggle-slider>
                </idx-block>
            </idx-form-group>
        </div>
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
            setItem: 'listingsSettings/setItem'
        })
    }
}
</script>
<style lang="scss">
@import '~bootstrap/scss/forms';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect';
@import '../styles/formContentStyles.scss';
.fieldset hr {
    border: 1px solid $gray-400;
    margin-bottom: var(--space-6);
    margin-top: var(--space-6);
    opacity: 1;
}
.control-label-title {
    display: block;
    margin-bottom: var(--space-1);
}
.listings-advanced__border {
    display: flex;
    flex-direction: column;
    grid-gap: 40px;
    padding-bottom: 40px;
    border-bottom: 1px solid $gray-400;
}

</style>
