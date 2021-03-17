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
                :formDisabled="formDisabled"
                v-bind="localStateValues"
                @form-field-update="formUpdate"
                @form-field-update-mls-membership="mlsChangeUpdate"
            />
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import guidedSetupMixin from '@/mixins/guidedSetup'
import pageGuard from '@/mixins/pageGuard'
import omnibarMixin from '@/mixins/omnibarMixin'
import omnibarForm from '@/templates/omnibarForm.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    name: 'guided-setup-omnibar',
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
            guidedSetupSteps: state => state.progressStepper.guidedSetupSteps
        })
    },
    methods: {
        ...mapActions({
            progressStepperUpdate: 'progressStepper/progressStepperUpdate'
        }),
        async goContinue () {
            this.formDisabled = true
            // await this.saveOmnibarSettings()
            this.formDisabled = false
            // to do: save handler
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
