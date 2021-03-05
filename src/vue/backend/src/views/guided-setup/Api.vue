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
            <APIKey
                :error="error"
                :loading="loading"
                :success="success"
                :apiKey="apiKey"
                @form-field-update="setItem($event)"
            />
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
import APIKey from '@/components/APIKey.vue'
export default {
    components: {
        APIKey,
        GuidedSetupContentCard
    },
    data () {
        return {
            error: false,
            loading: false,
            success: false
        }
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.progressStepper.guidedSetupSteps,
            apiKey: state => state.general.apiKey
        })
    },
    methods: {
        ...mapActions({
            setItem: 'general/setItem',
            progressStepperUpdate: 'progressStepper/progressStepperUpdate',
            verifyAPIkey: 'general/verifyAPIkey'
        }),
        goBackStep: function () {
            // to-do: go back in history
            this.$router.go(-1)
        },
        goSkipStep: function () {
            this.$router.push({ path: '/guided-setup/listings' })
        },
        async goContinue () {
            this.loading = true
            await this.verifyAPIkey()
            this.loading = false
            this.success = true
            this.cardTitle = 'Account Connected!'
            setTimeout(() => {
                this.$router.push({ path: '/guided-setup/connect/general' })
            }, 3000)
        }
    },
    created () {
        this.links = [
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
        this.errorMessage = 'We couldn’t find an account with the provided API key'
        this.cardTitle = 'Connect Your IDX Broker Account'
    },
    mounted () {
        this.progressStepperUpdate([1, 0, 0, 0])
    }
}
</script>
