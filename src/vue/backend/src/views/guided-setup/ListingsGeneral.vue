<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="goContinue">
        <template v-slot:controls>
            <ListingsGeneral/>
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import ListingsGeneral from '@/templates/ListingsGeneral.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    components: {
        ListingsGeneral,
        GuidedSetupContentCard
    },
    created () {
        this.cardTitle = 'Configure IMPress Listings'
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
    computed: {
        ...mapState({
            enabled: state => state.listingsSettings.enabled,
            guidedSetupSteps: state => state.general.guidedSetupSteps
        })
    },
    methods: {
        ...mapActions({
            listingsSettingsStateChange: 'listingsSettings/listingsSettingsStateChange',
            saveListingsGeneralSettings: 'general/saveListingsGeneralSettings'
        }),
        goBackStep: function () {
            // to-do: go back in history
            this.$router.go(-1)
        },
        goSkipStep: function () {
            this.$router.push({ path: '/guided-setup/agents' })
        },
        async goContinue () {
            await this.saveListingsGeneralSettings()
            this.$router.push({ path: '/guided-setup/listings/idx' })
        }
    }
}
</script>
