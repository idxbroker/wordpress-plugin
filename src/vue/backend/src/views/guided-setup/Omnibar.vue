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
                :cityListOptions="cityListOptions"
                :cityListSelected="cityListSelected"
                :countyListOptions="countyListOptions"
                :countyListSelected="countyListSelected"
                :postalCodeListOptions="postalCodeListOptions"
                :postalCodeSelected="postalCodeSelected"
                :defaultPropertyTypeOptions="defaultPropertyTypeOptions"
                :defaultPropertyTypeSelected="defaultPropertyTypeSelected"
                :mlsMembership="mlsMembership"
                :autofillMLS="autofillMLS"
                :customFieldsSelected="customFieldsSelected"
                :customFieldsOptions="customFieldsOptions"
                :customPlaceholder="customPlaceholder"
                :defaultSortOrderSelected="defaultSortOrderSelected"
                @form-field-update="setItem($event)"
            />
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import omnibarForm from '@/templates/omnibarForm.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    components: {
        omnibarForm,
        GuidedSetupContentCard
    },
    data () {
        return {
            cardTitle: 'IMPress Omnibar Search',
            links: [
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
        }
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
        goBackStep: function () {
            // to-do: go back in history
            this.$router.go(-1)
        },
        goSkipStep: function () {
            this.$router.push({ path: '/guided-setup/listings' })
        },
        async goContinue () {
            await this.saveOmnibarSettings()
            this.$router.push({ path: '/guided-setup/listings' })
        }
    },
    mounted () {
        this.progressStepperUpdate([3, 0, 0, 0])
    }
}
</script>
