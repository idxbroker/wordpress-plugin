<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="goContinue"
    >
        <template v-slot:controls>
            <GeneralSettings
                :reCAPTCHA="localStateValues.reCAPTCHA"
                :updateFrequency="localStateValues.updateFrequency"
                :wrapperName="localStateValues.wrapperName"
                @form-field-update="formUpdate"
            />
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import pageGuard from '@/mixins/pageGuard'
import GeneralSettings from '@/templates/GeneralSettings.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    mixins: [pageGuard],
    components: {
        GeneralSettings,
        GuidedSetupContentCard
    },
    data () {
        return {
            cardTitle: 'General Settings',
            links: [
                {
                    text: 'Dynamic Wrappers',
                    href: '#dynamic-wrappers'
                },
                {
                    text: 'IDX Broker Middleware',
                    href: 'https://middleware.idxbroker.com/mgmt/'
                },
                {
                    text: 'Sign up for IDX Broker',
                    href: '#signUp'
                }
            ]
        }
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.progressStepper.guidedSetupSteps,
            reCAPTCHA: state => state.general.reCAPTCHA,
            updateFrequency: state => state.general.updateFrequency,
            wrapperName: state => state.general.wrapperName
        })
    },
    methods: {
        ...mapActions({
            setItem: 'general/setItem',
            progressStepperUpdate: 'progressStepper/progressStepperUpdate',
            saveGeneralSettings: 'general/saveGeneralSettings'
        }),
        goBackStep: function () {
            // to-do: go back in history
            this.$router.go(-1)
        },
        goSkipStep: function () {
            this.$router.push({ path: '/guided-setup/listings' })
        },
        async goContinue () {
            await this.saveGeneralSettings()
            this.saveAction()
            this.$router.push({ path: '/guided-setup/connect/omnibar' })
        }
    },
    created () {
        this.module = 'general'
    },
    mounted () {
        this.progressStepperUpdate([2, 0, 0, 0])
    }
}
</script>
