<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="saveHandler('general')"
    >
        <template v-slot:controls>
            <GeneralSettings
                :formDisabled="formDisabled"
                v-bind="localStateValues"
                @form-field-update="formUpdate"
            />
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import pageGuard from '@/mixins/pageGuard'
import guidedSetupMixin from '@/mixins/guidedSetup'
import GeneralSettings from '@/templates/GeneralSettings.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    name: 'guided-setup-general',
    inject: [PRODUCT_REFS.general.repo],
    mixins: [pageGuard, guidedSetupMixin],
    components: {
        GeneralSettings,
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
            apiKeyIsValid: state => state.general.isValid
        })
    },
    methods: {
        ...mapActions({
            progressStepperUpdate: 'guidedSetup/progressStepperUpdate'
        })
    },
    async created () {
        this.module = 'general'
        this.cardTitle = 'General Settings'
        this.skipPath = this.apiKeyIsValid ? '/guided-setup/listings' : '/guided-setup/confirmation'
        this.continuePath = '/guided-setup/connect/omnibar'
        this.links = [
            {
                text: 'Setting up a wrapper',
                href: 'https://support.idxbroker.com/s/article/automatically-create-wordpress-dynamic-wrapper'
            }
        ]
        const { data } = await this.generalRepository.get()
        for (const key in data) {
            this.$store.dispatch(`${this.module}/setItem`, { key, value: data[key] })
        }
    },
    mounted () {
        this.progressStepperUpdate([2, 0, 0, 0])
    }
}
</script>
