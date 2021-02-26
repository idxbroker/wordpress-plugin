<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="goContinue">
        <template v-slot:description>
            <p>This step is optional. A sentence or two about why you should connect IMPress for IDX Broker to your IDX Broker account. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </template>
        <template v-slot:controls>
            <APIKey :error="error" :loading="loading" :success="success"/>
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
import APIKey from '@/components/APIKey.vue'
export default {
    name: 'GuidedSetupConnectApi',
    components: {
        APIKey,
        GuidedSetupContentCard
    },
    data () {
        return {
            cardTitle: 'Connect Your IDX Broker Account',
            error: false,
            errorMessage: 'We couldn’t find an account with the provided API key',
            loading: false,
            success: false,
            links: [
                {
                    text: 'Where can I find my API key?',
                    href: '#where'
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
            promiseWithTimeout: 'general/promiseWithTimeout'
        }),
        goBackStep: function () {
            // to-do: go back in history
            this.$router.go(-1)
        },
        goSkipStep: function () {
            this.$router.push({ path: '/guided-setup/connect/general' })
        },
        async goContinue () {
            this.loading = true
            await this.promiseWithTimeout()
            this.loading = false
            this.success = true
        }
    },
    watch: {
        success: async function () {
            if (this.success === true) {
                this.cardTitle = 'Account Connected!'
                await this.promiseWithTimeout()
                this.$router.push({ path: '/guided-setup/connect/general' })
            }
        }
    }
}
</script>
