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
            <idx-block className="control-label"><strong class="control-label-title">Google reCAPTCHA</strong> Google reCAPTCHA v3 helps to prevent spammers from filling out your forms.</idx-block>
            <idx-block className="control-toggle-slider">
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
            <idx-block className="control-label"><strong class="control-label-title">Update Frequency</strong> Choose how often IMPress gets updates from your IDX Broker account.</idx-block>
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
