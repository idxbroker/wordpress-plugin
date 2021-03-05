<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="goContinue">
        <template v-slot:controls>
            <ListingsGeneral
                :currencyCodeSelected="currencyCodeSelected"
                :currencySymbolSelected="currencySymbolSelected"
                :defaultDisclaimer="defaultDisclaimer"
                :numberOfPosts="numberOfPosts"
                :listingSlug="listingSlug"
                :defaultState="defaultState"
                @form-field-update="setItem($event)"
            />
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import GuidedSetupMixin from '@/mixins/guidedSetup'
import ListingsGeneral from '@/templates/impressListingsGeneralContent.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    mixins: [GuidedSetupMixin],
    components: {
        ListingsGeneral,
        GuidedSetupContentCard
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.progressStepper.guidedSetupSteps,
            currencyCodeSelected: state => state.listingsSettings.currencyCodeSelected,
            currencySymbolSelected: state => state.listingsSettings.currencySymbolSelected,
            defaultDisclaimer: state => state.listingsSettings.defaultDisclaimer,
            numberOfPosts: state => state.listingsSettings.numberOfPosts,
            listingSlug: state => state.listingsSettings.listingSlug,
            defaultState: state => state.listingsSettings.defaultState
        })
    },
    methods: {
        ...mapActions({
            setItem: 'listingsSettings/setItem',
            progressStepperUpdate: 'progressStepper/progressStepperUpdate',
            saveGeneralListingsSettings: 'listingsSettings/saveGeneralListingsSettings'
        }),
        async goContinue () {
            await this.saveGeneralListingsSettings()
            this.$router.push({ path: this.continuePath })
        }
    },
    created () {
        this.cardTitle = 'Configure IMPress Listings'
        this.continuePath = '/guided-setup/listings/idx'
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
        this.progressStepperUpdate([4, 2, 0, 0])
    }
}
</script>
