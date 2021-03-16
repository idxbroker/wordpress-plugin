<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="goContinue">
        <template v-slot:controls>
            <ListingsIdx
                :formDisabled="formDisabled"
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
import ListingsIdx from '@/templates/impressListingsIdxContent.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    name: 'guided-setup-listings-idx',
    mixins: [
        guidedSetupMixin,
        pageGuard
    ],
    components: {
        ListingsIdx,
        GuidedSetupContentCard
    },
    data () {
        return {
            formDisabled: false
        }
    },
    computed: {
        ...mapState({
            enabled: state => state.listingsSettings.enabled,
            guidedSetupSteps: state => state.progressStepper.guidedSetupSteps,
            updateListings: state => state.listingsSettings.updateListings,
            soldListings: state => state.listingsSettings.soldListings,
            automaticImport: state => state.listingsSettings.automaticImport,
            displayIDXLink: state => state.listingsSettings.displayIDXLink,
            defaultListingTemplateSelected: state => state.listingsSettings.defaultListingTemplateSelected,
            defaultListingTemplateOptions: state => state.listingsSettings.defaultListingTemplateOptions,
            importedListingsAuthorSelected: state => state.listingsSettings.importedListingsAuthorSelected,
            importedListingsAuthorOptions: state => state.listingsSettings.importedListingsAuthorOptions,
            importTitle: state => state.listingsSettings.importTitle,
            advancedFieldData: state => state.listingsSettings.advancedFieldData,
            displayAdvancedFields: state => state.listingsSettings.displayAdvancedFields
        })
    },
    methods: {
        ...mapActions({
            setItem: 'listingsSettings/setItem',
            progressStepperUpdate: 'progressStepper/progressStepperUpdate',
            saveIDXListingsSettings: 'listingsSettings/saveIDXListingsSettings'
        }),
        async goContinue () {
            this.formDisabled = true
            await this.saveIDXListingsSettings()
            this.saveAction()
            this.formDisabled = false
            this.$router.push({ path: this.continuePath })
        }
    },
    created () {
        this.module = 'listingsSettings'
        this.cardTitle = 'Configure IMPress Listings'
        this.continuePath = '/guided-setup/listings/advanced'
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
        this.progressStepperUpdate([4, 3, 0, 0])
    }
}
</script>
