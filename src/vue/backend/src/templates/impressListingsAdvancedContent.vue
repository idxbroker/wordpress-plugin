<template>
    <idx-block
        tag="fieldset"
        :className="{
            'form-content': true,
            'form-content--disabled': formDisabled
        }">
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">CSS Settings</idx-block>
            <p>Here you can deregister the WP Listings CSS files and move to your theme's css file for ease of customization.</p>
        </idx-block>
        <idx-form-group>
            <idx-block className="form-content__toggle">
                {{ mainCssLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="$emit('form-field-update', { key: 'deregisterMainCss', value: !deregisterMainCss })"
                    :active="deregisterMainCss"
                    :disabled="formDisabled"
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
                    :disabled="formDisabled"
                    :label="widgetsCssLabel"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <hr/>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Form Submissions to IDX Broker</idx-block>
            <p>Send all contact form submissions to IDX Broker as a lead.<br> <b>Note:</b> This option only works while using default contact forms.</p>
        </idx-block>
        <idx-form-group>
            <idx-block className="form-content__toggle">
                {{ sendFormLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="$emit('form-field-update', { key: 'sendFormSubmission', value: !sendFormSubmission })"
                    :active="sendFormSubmission"
                    :disabled="formDisabled"
                    :label="sendFormLabel"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Default Form Shortcode</idx-block>
            <p>
                If you use a Contact Form plugin, you may enter the form shortcode here to display
                on all listings. Additionally, each listing can use a custom form. If no short code
                is entered the template uses default contact forms.
            </p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}form-shortcode`">Form Shortcode</idx-form-label>
            <idx-form-input
                type="text"
                :id="`${$idxStrap.prefix}form-shortcode`"
                :disabled="formDisabled"
                :value="formShortcode"
                @change="$emit('form-field-update', { key: 'formShortcode', value: $event.target.value })"
            />
        </idx-form-group>
        <hr/>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Google Maps</idx-block>
            <p>Listings can be automatically mapped if a latitude and longitude is provided. A Google Maps API Key is required -
            <a target="_blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key">click here</a> to register.</p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}google-maps`">Google Maps API Key</idx-form-label>
            <idx-form-input
                type="text"
                :id="`${$idxStrap.prefix}google-maps`"
                :disabled="formDisabled"
                :value="googleMapsAPIKey"
                @change="$emit('form-field-update', { key: 'googleMapsAPIKey', value: $event.target.value })"
            />
        </idx-form-group>
        <hr/>
        <idx-block className="form-content__label">
            <idx-block tag="h2" className="form-content__title">Custom Wrapper</idx-block>
            <p>If your theme's content HTML ID's and Classes are different than the included template, you can enter the HTML of your content wrapper beginning and end.</p>
        </idx-block>
        <idx-form-group>
            <idx-block className="form-content__toggle">
                {{ useCustomWrapperLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="$emit('form-field-update', { key: 'useCustomWrapper', value: !useCustomWrapper })"
                    :active="useCustomWrapper"
                    :disabled="formDisabled"
                    :label="useCustomWrapperLabel"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}wrapper-start-html`">Wrapper Start HTML</idx-form-label>
            <idx-form-input
                :disabled="!useCustomWrapper || formDisabled"
                type="text"
                :id="`${$idxStrap.prefix}wrapper-start-html`"
                :value="wrapperStart"
                @change="$emit('form-field-update', { key: 'wrapperStart', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}wrapper-end-html`">Wrapper End HTML</idx-form-label>
            <idx-form-input
                :disabled="!useCustomWrapper || formDisabled"
                type="text"
                :id="`${$idxStrap.prefix}wrapper-end-html`"
                :value="wrapperEnd"
                @change="$emit('form-field-update', { key: 'wrapperEnd', value: $event.target.value })"
            />
        </idx-form-group>
        <hr/>
        <idx-block className="form-content__label">
            <idx-block tag="h2" className="form-content__title">Plugin Uninstallation</idx-block>
            <p>Checking this option will delete <b>all</b> plugin data when uninstalling the plugin.</p>
        </idx-block>
        <idx-form-group>
            <idx-block className="form-content__toggle">
                {{ pluginUninstallationLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="$emit('form-field-update', { key: 'deletePluginDataOnUninstall', value: !deletePluginDataOnUninstall })"
                    :active="deletePluginDataOnUninstall"
                    :disabled="formDisabled"
                    :label="pluginUninstallationLabel"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
    </idx-block>
</template>
<script>
export default {
    name: 'ListingsAdvanced',
    inheritAttrs: false,
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
        },
        useCustomWrapper: {
            type: Boolean,
            default: false
        },
        formDisabled: {
            type: Boolean,
            default: false
        }
    },
    created () {
        this.mainCssLabel = 'Deregister IMPress Listings Main CSS?'
        this.widgetsCssLabel = 'Deregister IMPress Listings Widgets CSS?'
        this.sendFormLabel = 'Send Form Submissions to IDX Broker'
        this.pluginUninstallationLabel = 'Delete Plugin Data on Uninstall'
        this.useCustomWrapperLabel = 'Use Custom Wrapper?'
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect';
</style>
