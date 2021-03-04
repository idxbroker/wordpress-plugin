<template>
    <idx-block tag="fieldset" className="general-settings form-content">
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="website-wrapper"><idx-block tag="h3" className="form-content__title">Name Your Global Website Wrapper</idx-block> Wrappers set the overall styling of your IDX Broker pages, some words about maintaining a consistent design between WordPress and IDX Broker.</idx-form-label>
            <idx-form-input
                type="text"
                id="website-wrapper"
                :value="wrapperName"
                @change="$emit('form-field-update',{ key: 'wrapperName', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="form-content__label"><idx-block tag="h3" className="form-content__title">Google reCAPTCHA</idx-block> Google reCAPTCHA v3 helps to prevent spammers from filling out your forms.</idx-block>
            <idx-block className="form-content__toggle">
                {{ toggleLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="$emit('form-field-update',{ key: 'reCAPTCHA', value: !reCAPTCHA })"
                    :active="reCAPTCHA"
                    :label="toggleLabel"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="form-content__label"><idx-block tag="h3" className="form-content__title">Update Frequency</idx-block> Choose how often IMPress gets updates from your IDX Broker account.</idx-block>
            <idx-custom-select
                ariaLabel="Update Frequency"
                :selected="updateFrequency"
                :options="updateFrequencyOptions"
                @selected-item="$emit('form-field-update', { key: 'updateFrequency', value: $event.value })"
            />
        </idx-form-group>
    </idx-block>
</template>
<script>

export default {
    name: 'GeneralSettings',
    data () {
        return {
            toggleLabel: 'Enable Google reCAPTCHA',
            updateFrequencyOptions: [
                { value: 'five_minutes', label: 'Every 5 Minutes' },
                { value: 'hourly', label: 'Hourly' },
                { value: 'daily', label: 'Daily' },
                { value: 'twice_daily', label: 'Twice Daily' },
                { value: 'weekly', label: 'Weekly' },
                { value: 'two_weeks', label: 'Every 2 Weeks' },
                { value: 'monthly', label: 'Monthly' },
                { value: 'disabled', label: 'Disabled' }
            ]
        }
    },
    props: {
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
        }
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider';
</style>
