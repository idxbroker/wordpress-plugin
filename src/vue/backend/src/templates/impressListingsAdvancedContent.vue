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
                        @toggle="$emit('form-field-update', { key: 'deregisterMainCss', value: !deregisterMainCss })"
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
                        @toggle="$emit('form-field-update', { key: 'deregisterWidgetCss', value: !deregisterWidgetCss })"
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
                        @toggle="$emit('form-field-update', { key: 'sendFormSubmission', value: !sendFormSubmission })"
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
                    @change="$emit('form-field-update', { key: 'formShortcode', value: $event.target.value })"
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
                    @change="$emit('form-field-update', { key: 'googleMapsAPIKey', value: $event.target.value })"
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
                    @change="$emit('form-field-update', { key: 'wrapperStart', value: $event.target.value })"
                />
            </idx-form-group>
            <idx-form-group>
                <idx-form-label customClass="control-label form-content__label" for="wrapper-end-html">Wrapper End HTML</idx-form-label>
                <idx-form-input
                    type="text"
                    id="wrapper-end-html"
                    :value="wrapperEnd"
                    @change="$emit('form-field-update', { key: 'wrapperEnd', value: $event.target.value })"
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
                        @toggle="$emit('form-field-update', { key: 'sendFormSubmission', value: !sendFormSubmission })"
                        :active="sendFormSubmission"
                        :label="pluginUninstallationLabel"
                    ></idx-toggle-slider>
                </idx-block>
            </idx-form-group>
        </div>
    </idx-block>
</template>
<script>
export default {
    name: 'ListingsAdvanced',
    props: {
        deregisterMainCss: {
            type: Boolean,
            default: false
        },
        deregisterWidgetCss: {
            type: Boolean,
            default: false
        },
        sendFormSubmission: {
            type: Boolean,
            default: true
        },
        formShortcode: {
            type: String,
            default: ''
        },
        googleMapsAPIKey: {
            type: String,
            default: ''
        },
        wrapperStart: {
            type: String,
            default: ''
        },
        wrapperEnd: {
            type: String,
            default: ''
        },
        deletePluginDataOnUninstall: {
            type: Boolean,
            default: false
        }
    },
    created () {
        this.mainCssLabel = 'Deregister IMPress Listings Main CSS?'
        this.widgetsCssLabel = 'Deregister IMPress Listings Widgets CSS?'
        this.sendFormLabel = 'Send Form Submissions to IDX Broker'
        this.pluginUninstallationLabel = 'Delete Plugin Data on Uninstall'
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect';
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
