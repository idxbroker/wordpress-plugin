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
import guidedSetupMixin from '@/mixins/guidedSetup'
import pageGuard from '@/mixins/pageGuard'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
import socialProForm from '@/templates/socialProForm.vue'
export default {
    name: 'guided-setup-social-pro-configure',
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
    created () {
        this.module = 'socialPro'
        this.cardTitle = 'Social Syndication Settings'
        this.continuePath = '/guided-setup/confirmation'
        this.skipPath = '/guided-setup/confirmation'
    },
    mounted () {
        this.progressStepperUpdate([4, 5, 3, 2])
    }
}
</script>
