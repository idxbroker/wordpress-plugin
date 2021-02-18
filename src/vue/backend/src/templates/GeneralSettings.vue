<template>
    <idx-block className="general-settings">
        <idx-form-group>
            <idx-form-label for="website-wrapper"><strong>Name Your Global Website Wrapper</strong><br> Wrappers set the overall styling of your IDX Broker pages, some words about maintaining a consistent design between WordPress and IDX Broker.</idx-form-label>
            <idx-form-input
                type="text"
                id="website-wrapper"
                :value="wrapperName"
                @change="generalSettingsStateChange({ key: 'wrapperName', value: $event })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-label"><strong>Google reCAPTCHA</strong><br> Google reCAPTCHA v3 helps to prevent spammers from filling out your forms.</idx-block>
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
            <idx-form-label for="update-frequency"><strong>Update Frequency</strong><br> Choose how often IMPress gets updates from your IDX Broker account.</idx-form-label>
            <idx-custom-select
                uniqueID="update-frequency"
                ariaLabel="Select frequency"
                :selected="updateFrequency"
                :options="updateFrequencyOptions"
                @selected-item="generalSettingsStateChange({ key: 'updateFrequency', value: $event })"
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
                { name: '1 Minute' },
                { name: '2 Minutes' },
                { name: '3 Minutes' },
                { name: '4 Minutes' },
                { name: '5 Minutes' }
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
.general-settings {
    // Spacing
    --space-4: 16px;
    --space-6: 24px;
    --space-8: 32px;
    --space-9: 36px;
    --space-10: 40px;
    --space-12: 48px;
    --space-15: 60px;
    --space-18: 72px;
    // Typography
    --font-size-h1: 31px;
    --font-size-h2: 25px;
    --font-size-p: 16px;
    --font-size-p-large: 18px;
    --line-height-h1: 28px;
    --line-height-h2: 28px;
    --line-height-p: 28px;
    --line-height-p-large: 28px;
    font-size: var(--font-size-p);
    height: 100%;
    line-height: var(--line-height-p);
    margin-left: -20px;
    min-height: 100vh;
    position: relative;
    width: calc(100% + 20px);
    h1,h2,h3,h4,h5,h6 {
        // reset styles
        color: inherit;
        display: block;
        float: none;
    }
    h1 {
        font-size: var(--font-size-h1);
        line-height: var(--line-height-h1);
    }
    h2 {
        font-size: var(--font-size-h2);
        line-height: var(--line-height-h2);
    }
    p {
        font-size: inherit;
        line-height: inherit;
    }
    .control-toggle-slider {
        align-items: center;
        background-color: $gray-150;
        display: flex;
        justify-content: space-between;
        padding: 12px 20px;
    }
}
</style>
