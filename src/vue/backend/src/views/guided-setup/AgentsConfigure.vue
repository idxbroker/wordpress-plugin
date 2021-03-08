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
            setItem: 'agentSettings/setItem',
            progressStepperUpdate: 'progressStepper/progressStepperUpdate',
            saveConfigureAgentSettings: 'agentSettings/saveConfigureAgentSettings'
        }),
        async goContinue () {
            await this.saveConfigureAgentSettings()
            this.saveAction()
            this.$router.push({ path: this.continuePath })
        }
    },
    created () {
        this.module = 'agentSettings'
        this.cardTitle = 'Configure IMPress Agents'
        this.continuePath = '/guided-setup/social-pro'
        this.skipPath = '/guided-setup/social-pro'
        this.links = [
            {
                text: 'IMPress Agents Features',
                href: '#agents-features'
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
        this.progressStepperUpdate([4, 5, 2, 0])
    }
}
</script>
