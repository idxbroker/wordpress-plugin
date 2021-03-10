<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="goContinue">
        <template v-slot:controls>
            <AgentsSettings
                v-bind="localStateValues"
                @form-field-update="formUpdate"
            />
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import guidedSetupMixin from '@/mixins/guidedSetup'
import pageGuard from '@/mixins/pageGuard'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
import AgentsSettings from '@/templates/AgentsSettings.vue'
export default {
    mixins: [
        guidedSetupMixin,
        pageGuard
    ],
    components: {
        AgentsSettings,
        GuidedSetupContentCard
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.progressStepper.guidedSetupSteps
        })
    },
    methods: {
        ...mapActions({
            setItem: 'socialPro/setItem',
            progressStepperUpdate: 'progressStepper/progressStepperUpdate',
            saveConfigureSocialProSettings: 'socialPro/saveConfigureSocialProSettings'
        }),
        async goContinue () {
            await this.saveConfigureSocialProSettings()
            this.saveAction()
            this.$router.push({ path: this.continuePath })
        }
    },
    created () {
        this.module = 'socialPro'
        this.cardTitle = 'Social Syndication Settings'
        this.continuePath = '/guided-setup/confirmation'
        this.skipPath = '/guided-setup/confirmation'
        this.links = [
            {
                text: 'Social Pro with IDX Broker',
                href: '#social-pro'
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
    },
    mounted () {
        this.progressStepperUpdate([4, 5, 3, 2])
    }
}
</script>
