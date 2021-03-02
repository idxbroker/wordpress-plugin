<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="goContinue">
        <template v-slot:controls>
            <GeneralSettings/>
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import GeneralSettings from '@/templates/GeneralSettings.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    components: {
        GeneralSettings,
        GuidedSetupContentCard
    },
    data () {
        return {
            cardTitle: 'General Settings',
            links: [
                {
                    text: 'Dynamic Wrappers',
                    href: '#dynamic-wrappers'
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
        }
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.general.guidedSetupSteps
        })
    },
    methods: {
        ...mapActions({
            generalSettingsStateChange: 'general/generalSettingsStateChange',
            saveGeneralSettings: 'general/saveGeneralSettings'
        }),
        goBackStep: function () {
            // to-do: go back in history
            this.$router.go(-1)
        },
        goSkipStep: function () {
            this.$router.push({ path: '/guided-setup/listings' })
        },
        async goContinue () {
            await this.saveGeneralSettings()
            this.$router.push({ path: '/guided-setup/connect/omnibar' })
        }
    }
}
</script>
