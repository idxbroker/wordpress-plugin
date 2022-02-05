<template>
    <idx-block
        tag="fieldset"
        :className="{
            'form-content': true,
            'form-content--disabled': formDisabled
        }">
        <idx-form-group>
            <idx-block className="form-content__label">
                <idx-block
                    :id="`${$idxStrap.prefix}wrapperNameLabel`"
                    tag="h2"
                    className="form-content__title"
                >
                    Create Your Global Website Wrapper
                </idx-block>
                <p>
                    Creating your Global Website Wrapper will set the overall styling of
                    your IDX Broker pages. Setting this up will match the IDX pages to your
                    website design automatically every few hours.
                </p>
            </idx-block>
            <idx-form-input
                :aria-labelledby="`${$idxStrap.prefix}wrapperNameLabel`"
                type="text"
                :disabled="formDisabled"
                :id="`${$idxStrap.prefix}wrapperName`"
                :value="wrapperName"
                @change="$emit('form-field-update',{ key: 'wrapperName', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="form-content__label">
                <idx-block tag="h2" className="form-content__title">Google reCAPTCHA</idx-block>
                <p>Google reCAPTCHA v3 helps to prevent spammers from filling out your forms.</p>
            </idx-block>
            <idx-block className="form-content__toggle">
                {{ toggleLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    :active="reCAPTCHA"
                    :disabled="formDisabled"
                    :label="toggleLabel"
                    @toggle="$emit('form-field-update',{ key: 'reCAPTCHA', value: !reCAPTCHA })"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="form-content__label">
                <idx-block tag="h2" className="form-content__title">Update Frequency</idx-block>
                <p>Choose how often IMPress gets updates from your IDX Broker account.</p>
            </idx-block>
            <idx-custom-select
                ariaLabel="Update Frequency"
                :disabled="formDisabled"
                :selected="updateFrequency"
                :options="updateFrequencyOptions"
                @selected-item="$emit('form-field-update', { key: 'updateFrequency', value: $event.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="form-content__label">
                <idx-block tag="h2" className="form-content__title">Install Information Data Collection</idx-block>
                <p>IDX Broker collects general install information to help improve our WordPress plugins.</p>
            </idx-block>
            <idx-block className="form-content__toggle">
                {{ dataCollectionLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    :active="optOut"
                    :disabled="formDisabled"
                    :label="dataCollectionLabel"
                    @toggle="$emit('form-field-update',{ key: 'optOut', value: !optOut })"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
    </idx-block>
</template>
<script>

export default {
    name: 'GeneralSettings',
    inheritAttrs: false,
    props: {
        optOut: {
            type: Boolean,
            default: false
        },
        reCAPTCHA: {
            type: Boolean,
            default: false
        },
        updateFrequency: {
            type: String,
            default: 'five_minutes'
        },
        wrapperName: {
            type: String,
            default: ''
        },
        formDisabled: {
            type: Boolean,
            default: false
        }
    },
    created () {
        this.toggleLabel = 'Enable Google reCAPTCHA'
        this.dataCollectionLabel = 'Opt-Out'
        this.updateFrequencyOptions = [
            { value: 'five_minutes', label: 'Every 5 Minutes' },
            { value: 'hourly', label: 'Hourly' },
            { value: 'twice_daily', label: 'Twice Daily' },
            { value: 'daily', label: 'Daily' },
            { value: 'weekly', label: 'Weekly' },
            { value: 'two_weeks', label: 'Every 2 Weeks' },
            { value: 'monthly', label: 'Monthly' },
            { value: 'disabled', label: 'Disabled' }
        ]
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider';
</style>
