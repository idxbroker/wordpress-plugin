<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="saveHandler">
        <template v-slot:description>
            <p><strong>This step is optional.</strong> A sentence or two about why you should connect IMPress for IDX Broker to your IDX Broker account. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </template>
        <template v-slot:controls>
            <idx-block className="form-content">
                <idx-form-group>
                    <idx-form-label customClass="form-content__label" for="ApiKey">API Key</idx-form-label>
                    <APIKey
                        :error="error"
                        :loading="loading"
                        :success="success"
                        :apiKey="localStateValues.apiKey"
                        @form-field-update="formUpdate"
                    />
                </idx-form-group>
            </idx-block>
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import pageGuard from '@/mixins/pageGuard'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
import APIKey from '@/components/APIKey.vue'
export default {
    name: 'guided-setup-api',
    inject: [PRODUCT_REFS.general.repo],
    mixins: [pageGuard],
    components: {
        APIKey,
        GuidedSetupContentCard
    },
    data () {
        return {
            error: false,
            loading: false,
            success: false,
            cardTitle: 'Connect Your IDX Broker Account'
        }
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
        goBackStep () {
            // to-do: go back in history
            this.$router.go(-1)
        },
        goSkipStep () {
            this.$router.push({ path: '/guided-setup/listings' })
        },
        continue () {
            this.success = true
            this.cardTitle = 'Account Connected!'
            setTimeout(() => {
                this.$router.push({ path: '/guided-setup/connect/general' })
            }, 3000)
        },
        async saveHandler () {
            // To Do: user facing error checking
            if (this.formChanges) {
                this.loading = true
                try {
                    await this.generalRepository.post(this.formChanges)
                    this.saveAction()
                    this.continue()
                } catch (error) {
                    this.error = true
                    this.success = false
                }
                this.loading = false
            } else {
                this.continue()
            }
        }
    },
    async created () {
        this.module = 'general'
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
        const { data } = await this.generalRepository.get()
        for (const key in data) {
            this.$store.dispatch(`${this.module}/setItem`, { key, value: data[key] })
        }
    },
    mounted () {
        this.progressStepperUpdate([1, 0, 0, 0])
    }
}
</script>
