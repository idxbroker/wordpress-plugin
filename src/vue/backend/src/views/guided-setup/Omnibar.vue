<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="saveHandler('omnibar')">
        <template v-slot:controls>
            <omnibarForm
                :formDisabled="formDisabled"
                v-bind="localStateValues"
                @form-field-update="formUpdate"
                @form-field-update-mls-membership="mlsChangeUpdate"
            />
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { PRODUCT_REFS } from '@/data/productTerms'
import { mapState, mapActions } from 'vuex'
import guidedSetupMixin from '@/mixins/guidedSetup'
import pageGuard from '@/mixins/pageGuard'
import omnibarMixin from '@/mixins/omnibarMixin'
import omnibarForm from '@/templates/omnibarForm.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    name: 'guided-setup-omnibar',
    inject: [PRODUCT_REFS.omnibar.repo],
    mixins: [
        guidedSetupMixin,
        pageGuard,
        omnibarMixin
    ],
    components: {
        omnibarForm,
        GuidedSetupContentCard
    },
    data () {
        return {
            formDisabled: false
        }
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.guidedSetup.guidedSetupSteps,
            isValid: state => state.general.isValid
        })
    },
    methods: {
        ...mapActions({
            progressStepperUpdate: 'guidedSetup/progressStepperUpdate'
        })
    },
    async created () {
        this.module = 'omnibar'
        this.cardTitle = 'IMPress Omnibar Search'
        this.continuePath = this.isValid ? '/guided-setup/listings' : '/guided-setup/confirmation'
        this.skipPath = '/guided-setup/listings'
        this.links = [
            {
                text: 'IMPress Omnibar FAQs',
                href: '#omnibar-faqs'
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
        this.formDisabled = true
        const { data } = await this.omnibarRepository.get()
        for (const key in data) {
            this.$store.dispatch(`${this.module}/setItem`, { key, value: data[key] })
        }
        this.formDisabled = false
    },
    mounted () {
        this.progressStepperUpdate([3, 0, 0, 0])
    }
}
</script>
