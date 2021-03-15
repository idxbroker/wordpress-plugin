<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="saveHandler">
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
            formDisabled: true
        }
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
        async saveHandler () {
            this.formDisabled = true
            if (this.formChanges) {
                const { status } = await this.agentSettingsRepository.post(this.formChanges)
                if (status === 200) {
                    this.saveAction()
                    this.$router.push({ path: this.continuePath })
                } else {
                    this.formDisabled = false
                    // To do: user feedback
                }
            } else {
                this.$router.push({ path: this.continuePath })
            }
        }
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
        this.formDisabled = false
        this.progressStepperUpdate([4, 5, 2, 0])
    }
}
</script>
