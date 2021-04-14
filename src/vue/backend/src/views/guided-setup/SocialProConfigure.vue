<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="saveHandler('socialPro')">
        <template v-slot:controls>
            <socialProForm
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
import guidedSetupMixin from '@/mixins/guidedSetup'
import pageGuard from '@/mixins/pageGuard'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
import socialProForm from '@/templates/socialProForm.vue'
export default {
    name: 'guided-setup-social-pro-configure',
    inject: [PRODUCT_REFS.socialPro.repo],
    mixins: [
        guidedSetupMixin,
        pageGuard
    ],
    components: {
        socialProForm,
        GuidedSetupContentCard
    },
    data () {
        return {
            formDisabled: false
        }
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.guidedSetup.guidedSetupSteps
        })
    },
    methods: {
        ...mapActions({
            progressStepperUpdate: 'guidedSetup/progressStepperUpdate'
        })
    },
    async created () {
        this.module = 'socialPro'
        this.cardTitle = 'Social Syndication Settings'
        this.continuePath = '/guided-setup/confirmation'
        this.skipPath = '/guided-setup/confirmation'
        this.formDisabled = true
        const { data } = await this.socialProRepository.get()
        for (const key in data) {
            this.$store.dispatch(`${this.module}/setItem`, { key, value: data[key] })
        }
        this.formDisabled = false
    },
    mounted () {
        this.progressStepperUpdate([4, 5, 3, 2])
    }
}
</script>
