<template>
    <idx-block tag="fieldset" className="fieldset general-settings form-content">
        <idx-form-group>
            <idx-form-label customClass="control-label" for="website-wrapper"><strong>Name Your Global Website Wrapper</strong> Wrappers set the overall styling of your IDX Broker pages, some words about maintaining a consistent design between WordPress and IDX Broker.</idx-form-label>
            <idx-form-input
                type="text"
                id="website-wrapper"
                :value="wrapperName"
                @change="generalSettingsStateChange({ key: wrapperName, value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-label"><strong>Google reCAPTCHA</strong> Google reCAPTCHA v3 helps to prevent spammers from filling out your forms.</idx-block>
            <idx-block className="control-toggle-slider form-content__toggle">
                {{ toggleLabel }}
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="generalSettingsStateChange({ key: 'reCAPTCHA', value: !reCAPTCHA })"
                    :active="reCAPTCHA"
                    :label="toggleLabel"
                ></idx-toggle-slider>
            </idx-block>
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-label"><strong>Update Frequency</strong> Choose how often IMPress gets updates from your IDX Broker account.</idx-block>
            <idx-custom-select
                ariaLabel="Update Frequency"
                :selected="updateFrequency"
                :options="updateFrequencyOptions"
                @selected-item="generalSettingsStateChange({ key: 'updateFrequency', value: $event.value })"
            />
        </idx-form-group>
    </idx-block>
</template>
<script>
import { mapState, mapActions } from 'vuex'
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
    computed: {
        ...mapState({
            reCAPTCHA: state => state.general.reCAPTCHA,
            updateFrequency: state => state.general.updateFrequency,
            wrapperName: state => state.general.wrapperName
        })
    },
    methods: {
        ...mapActions({
            generalSettingsStateChange: 'general/generalSettingsStateChange'
        })
    }
}
</script>
<style lang="scss">
@import '~bootstrap/scss/forms';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider.scss';
@import '../styles/formContentStyles.scss';
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
}
</style>
