<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="saveHandler('listingsIdx', 'listingsSettings', 'idx')">
        <template v-slot:controls>
            <ListingsIdx
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
import ListingsIdx from '@/templates/impressListingsIdxContent.vue'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    name: 'guided-setup-listings-idx',
    inject: [PRODUCT_REFS.listingsSettings.repo],
    mixins: [
        guidedSetupMixin,
        pageGuard
    ],
    components: {
        ListingsIdx,
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
        this.module = 'listingsIdx'
        this.cardTitle = 'Configure IMPress Listings'
        this.continuePath = '/guided-setup/listings/advanced'
        this.skipPath = '/guided-setup/agents'
        this.links = [
            {
                text: 'IMPress Listings Features',
                href: '#listings-features'
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
        const { data } = await this.listingsSettingsRepository.get('idx')
        this.updateState(data)
    },
    mounted () {
        this.progressStepperUpdate([4, 3, 0, 0])
    }
}
</script>
