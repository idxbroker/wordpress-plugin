<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="goContinue">
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
import guidedSetupMixin from '@/mixins/guidedSetup'
import pageGuard from '@/mixins/pageGuard'
import ListingsAdvanced from '@/templates/impressListingsAdvancedContent.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    name: 'guided-setup-listings-advanced',
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
            guidedSetupSteps: state => state.progressStepper.guidedSetupSteps,
            deregisterMainCss: state => state.listingsSettings.deregisterMainCss,
            deregisterWidgetCss: state => state.listingsSettings.deregisterWidgetCss,
            sendFormSubmission: state => state.listingsSettings.sendFormSubmission,
            formShortcode: state => state.listingsSettings.formShortcode,
            googleMapsAPIKey: state => state.listingsSettings.googleMapsAPIKey,
            wrapperStart: state => state.listingsSettings.wrapperStart,
            wrapperEnd: state => state.listingsSettings.wrapperEnd,
            deletePluginDataOnUninstall: state => state.listingsSettings.deletePluginDataOnUninstall
        })
    },
    methods: {
        ...mapActions({
            setItem: 'listingsSettings/setItem',
            progressStepperUpdate: 'progressStepper/progressStepperUpdate',
            saveAdvancedListingsSettings: 'listingsSettings/saveAdvancedListingsSettings'
        }),
        async goContinue () {
            await this.saveAdvancedListingsSettings()
            this.saveAction()
            this.$router.push({ path: this.continuePath })
        }
    },
    created () {
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
    },
    mounted () {
        this.progressStepperUpdate([4, 4, 0, 0])
    }
}
</script>
