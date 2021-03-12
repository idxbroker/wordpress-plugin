<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="saveHandler">
        <template v-slot:controls>
            <ListingsAdvanced
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
import ListingsAdvanced from '@/templates/impressListingsAdvancedContent.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    name: 'guided-setup-listings-advanced',
    inject: [PRODUCT_REFS.listingsSettings.repo],
    mixins: [
        guidedSetupMixin,
        pageGuard
    ],
    components: {
        ListingsAdvanced,
        GuidedSetupContentCard
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.progressStepper.guidedSetupSteps
        })
    },
    methods: {
        ...mapActions({
            progressStepperUpdate: 'progressStepper/progressStepperUpdate'
        }),
        async saveHandler () {
            if (this.formIsUpdated) {
                const { status } = await this.listingsSettingsRepository.post(this.formChanges, 'advanced')
                if (status === 204) {
                    this.saveAction()
                    this.$router.push({ path: this.continuePath })
                } else {
                    // To do: user feedback
                }
            } else {
                this.$router.push({ path: this.continuePath })
            }
        }
    },
    async created () {
        this.module = 'listingsSettings'
        this.cardTitle = 'Configure IMPress Listings'
        this.continuePath = '/guided-setup/agents'
        this.skipPath = '/guided-setup/agents'
        this.links = [
            {
                text: 'IMPress Listings Features',
                href: '#listings-features'
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
        const { data } = await this.listingsSettingsRepository.get('advanced')
        this.updateState(data)
    },
    mounted () {
        this.progressStepperUpdate([4, 4, 0, 0])
    }
}
</script>
