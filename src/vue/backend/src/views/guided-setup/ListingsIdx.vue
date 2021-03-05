<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="goContinue">
        <template v-slot:controls>
            <ListingsIdx/>
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import ListingsIdx from '@/templates/impressListingsIdxContent.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    components: {
        ListingsIdx,
        GuidedSetupContentCard
    },
    computed: {
        ...mapState({
            enabled: state => state.listingsSettings.enabled,
            guidedSetupSteps: state => state.progressStepper.guidedSetupSteps
        })
    },
    methods: {
        ...mapActions({
            progressStepperUpdate: 'progressStepper/progressStepperUpdate',
            saveIDXListingsSettings: 'listingsSettings/saveIDXListingsSettings'
        }),
        goBackStep: function () {
            this.$router.go(-1)
        },
        goSkipStep: function () {
            this.$router.push({ path: this.skipPath })
        },
        async goContinue () {
            await this.saveIDXListingsSettings()
            this.$router.push({ path: this.continuePath })
        }
    },
    created () {
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
