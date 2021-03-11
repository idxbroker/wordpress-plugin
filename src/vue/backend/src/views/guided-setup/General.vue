<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="saveHandler"
    >
        <template v-slot:controls>
            <GeneralSettings
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
import GeneralSettings from '@/templates/GeneralSettings.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    name: 'guided-setup-general',
    inject: [PRODUCT_REFS.general.repo],
    mixins: [pageGuard],
    components: {
        GeneralSettings,
        GuidedSetupContentCard
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
        goBackStep: function () {
            // to-do: go back in history
            this.$router.go(-1)
        },
        goSkipStep: function () {
            this.$router.push({ path: '/guided-setup/listings' })
        },
        async saveHandler () {
            if (this.formChanges) {
                const { status } = await this.generalRepository.post(this.formChanges)
                if (status === 200) {
                    this.saveAction()
                    this.$router.push({ path: '/guided-setup/connect/omnibar' })
                } else {
                    // To Do: form error handler
                }
            } else {
                this.$router.push({ path: '/guided-setup/connect/omnibar' })
            }
        }
    },
    async created () {
        this.module = 'general'
        this.cardTitle = 'General Settings'
        this.links = [
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
