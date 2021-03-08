<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="goContinue">
        <template v-slot:controls>
            <omnibarForm
                v-bind="localStateValues"
                @form-field-update="formUpdate"
            />
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import guidedSetupMixin from '@/mixins/guidedSetup'
import pageGuard from '@/mixins/pageGuard'
import omnibarForm from '@/templates/omnibarForm.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    mixins: [
        guidedSetupMixin,
        pageGuard
    ],
    components: {
        omnibarForm,
        GuidedSetupContentCard
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.progressStepper.guidedSetupSteps,
            cityListOptions: state => state.omnibar.cityListOptions,
            cityListSelected: state => state.omnibar.cityListSelected,
            countyListOptions: state => state.omnibar.countyListOptions,
            countyListSelected: state => state.omnibar.countyListSelected,
            postalCodeListOptions: state => state.omnibar.postalCodeListOptions,
            postalCodeSelected: state => state.omnibar.postalCodeSelected,
            defaultPropertyTypeOptions: state => state.omnibar.defaultPropertyTypeOptions,
            defaultPropertyTypeSelected: state => state.omnibar.defaultPropertyTypeSelected,
            mlsMembership: state => state.omnibar.mlsMembership,
            autofillMLS: state => state.omnibar.autofillMLS,
            customFieldsSelected: state => state.omnibar.customFieldsSelected,
            customFieldsOptions: state => state.omnibar.customFieldsOptions,
            customPlaceholder: state => state.omnibar.customPlaceholder,
            defaultSortOrderSelected: state => state.omnibar.defaultSortOrderSelected
        })
    },
    methods: {
        ...mapActions({
            generalSettingsStateChange: 'general/generalSettingsStateChange',
            setItem: 'omnibar/setItem',
            progressStepperUpdate: 'progressStepper/progressStepperUpdate',
            saveOmnibarSettings: 'general/saveOmnibarSettings'
        }),
        async goContinue () {
            await this.saveOmnibarSettings()
            this.saveAction()
            this.$router.push({ path: this.continuePath })
        }
    },
    created () {
        this.module = 'omnibar'
        this.cardTitle = 'IMPress Omnibar Search'
        this.continuePath = '/guided-setup/listings'
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
    },
    mounted () {
        this.progressStepperUpdate([3, 0, 0, 0])
    }
}
</script>
