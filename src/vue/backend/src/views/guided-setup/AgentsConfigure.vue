<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="saveHandler('agentSettings')">
        <template v-slot:controls>
            <AgentsSettings
                :formDisabled="formDisabled"
                v-bind="localStateValues"
                @form-field-update="formUpdate"
            />
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import guidedSetupMixin from '@/mixins/guidedSetup'
import pageGuard from '@/mixins/pageGuard'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
import AgentsSettings from '@/templates/AgentsSettings.vue'
export default {
    inject: [PRODUCT_REFS.agentSettings.repo],
    name: 'guided-setup-agents-configure',
    mixins: [
        guidedSetupMixin,
        pageGuard
    ],
    components: {
        AgentsSettings,
        GuidedSetupContentCard
    },
    data () {
        return {
            formDisabled: false
        }
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.guidedSetup.guidedSetupSteps
        })
    },
    methods: {
        ...mapActions({
            progressStepperUpdate: 'guidedSetup/progressStepperUpdate'
        })
    },
    async created () {
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
        const { data } = await this.agentSettingsRepository.get()
        this.updateState(data)
    },
    mounted () {
        this.progressStepperUpdate([4, 5, 2, 0])
    }
}
</script>
