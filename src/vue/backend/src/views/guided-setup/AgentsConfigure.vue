<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
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
            guidedSetupSteps: state => state.guidedSetup.guidedSetupSteps,
            restrictedByBeta: state => state.socialPro.restrictedByBeta,
            optedInBeta: state => state.socialPro.optedInBeta
        }),
        skipPath () {
            if ((this.restrictedByBeta && this.optedInBeta) || !this.restrictedByBeta) {
                return '/guided-setup/social-pro'
            } else {
                return '/guided-setup/confirmation'
            }
        },
        continuePath () {
            if ((this.restrictedByBeta && this.optedInBeta) || !this.restrictedByBeta) {
                return '/guided-setup/social-pro'
            } else {
                return '/guided-setup/confirmation'
            }
        }
    },
    methods: {
        ...mapActions({
            progressStepperUpdate: 'guidedSetup/progressStepperUpdate'
        })
    },
    async created () {
        this.module = 'agentSettings'
        this.cardTitle = 'Configure IMPress Agents'
        const { data } = await this.agentSettingsRepository.get()
        this.updateState(data)
    },
    mounted () {
        this.progressStepperUpdate([4, 5, 2, 0])
    }
}
</script>
