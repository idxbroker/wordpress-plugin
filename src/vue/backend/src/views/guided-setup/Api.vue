<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="saveHandler">
        <template v-slot:description>
            <p>{{ description }}</p>
        </template>
        <template v-slot:controls>
            <idx-block className="form-content">
                <idx-form-group>
                    <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}ApiKey`">API Key</idx-form-label>
                    <APIKey
                        :disabled="formDisabled"
                        :error="error"
                        :loading="formDisabled"
                        :success="success"
                        :apiKey="localStateValues.apiKey"
                        :devPartnerKey="localStateValues.devPartnerKey"
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
import guidedSetup from '@/mixins/guidedSetup'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
import APIKey from '@/components/APIKey.vue'
export default {
    name: 'guided-setup-api',
    inject: [PRODUCT_REFS.general.repo],
    mixins: [pageGuard, guidedSetup],
    components: {
        APIKey,
        GuidedSetupContentCard
    },
    data () {
        return {
            formDisabled: false,
            error: false,
            success: false,
            cardTitle: 'Connect Your IDX Broker Account',
            description: 'By providing your API Key, you’ll have access to all your IDX Broker data, including listing, agent, and office data within WordPress. If you do not have an IDX Broker account, skip this step and enter your data manually.'
        }
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.guidedSetup.guidedSetupSteps
        }),
        skipPath () {
            return this.error ? '/guided-setup/confirmation' : '/guided-setup/listings'
        }
    },
    methods: {
        ...mapActions({
            progressStepperUpdate: 'guidedSetup/progressStepperUpdate'
        }),
        continue () {
            this.success = true
            this.cardTitle = 'Account Connected!'
            this.description = 'You now have access to all your IDX Broker data, including listing, agent, and office data, within WordPress.'
            setTimeout(() => {
                this.$router.push({ path: '/guided-setup/connect/general' })
            }, 3000)
        },
        async saveHandler () {
            this.formDisabled = true
            this.error = false
            this.success = false
            if (this.formIsUpdated) {
                try {
                    await this.generalRepository.post(this.formChanges)
                    this.formDisabled = false
                    this.saveAction()
                    this.$store.dispatch(`${this.module}/setItem`, { key: 'isValid', value: true })
                    this.continue()
                } catch (error) {
                    this.formDisabled = false
                    this.error = true
                    this.success = false
                }
            } else {
                this.$router.push({ path: '/guided-setup/connect/general' })
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
