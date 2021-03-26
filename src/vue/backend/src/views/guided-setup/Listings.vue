<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="enablePlugin">
        <template v-slot:description>
            <p>
                Add your real estate listings manually or import them from your MLS Data feed.
                Once setup is complete, add your listings to widgets or display them directly
                on webpages using shortcodes.
            </p>
        </template>
        <template v-slot:controls>
            <idx-block className="form-content">
                <idx-form-group>
                    <idx-block className="form-content__toggle">
                        {{ activateLabel }}
                        <idx-toggle-slider
                            uncheckedState="No"
                            checkedState="Yes"
                            @toggle="formUpdate({ key: 'enabled', value: !localStateValues.enabled })"
                            :active="localStateValues.enabled"
                            :disabled="formDisabled"
                            :label="activateLabel"
                        ></idx-toggle-slider>
                    </idx-block>
                </idx-form-group>
                <idx-block v-if="showError" className="form-content__error">
                    We're experiencing a problem, please try again.
                </idx-block>
            </idx-block>
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { PRODUCT_REFS } from '@/data/productTerms'
import { mapActions, mapState } from 'vuex'
import guidedSetupMixin from '@/mixins/guidedSetup'
import pageGuard from '@/mixins/pageGuard'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    name: 'guided-setup-listings',
    inject: [PRODUCT_REFS.listingsSettings.repo],
    mixins: [
        guidedSetupMixin,
        pageGuard
    ],
    components: {
        GuidedSetupContentCard
    },
    data () {
        return {
            formDisabled: false,
            showError: false
        }
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.guidedSetup.guidedSetupSteps
        }),
        continuePath () {
            return this.localStateValues.enabled ? '/guided-setup/listings/general' : this.skipPath
        }
    },
    methods: {
        ...mapActions({
            progressStepperUpdate: 'guidedSetup/progressStepperUpdate'
        }),
        async enablePlugin () {
            this.showError = false
            this.formDisabled = true
            if (this.formIsUpdated) {
                const { status } = await this.listingsSettingsRepository.post({ enabled: this.localStateValues.enabled }, 'enable')
                this.formDisabled = false
                if (status === 204) {
                    this.saveAction()
                    this.$router.push({ path: this.continuePath })
                } else {
                    this.showError = true
                    this.formChanges = {}
                }
            } else {
                this.$router.push({ path: this.continuePath })
            }
        }
    },
    created () {
        this.module = 'listingsGeneral'
        this.cardTitle = 'Enable IMPress Listings'
        this.activateLabel = 'Enable'
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
    },
    mounted () {
        this.progressStepperUpdate([4, 1, 0, 0])
    }
}
</script>

<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider.scss';
@import '@/styles/formContentStyles.scss';
.list-featured {
    column-count: 2;
    font-weight: 700;
    list-style-type: circle;
    padding-left: 1.125em;
}
</style>
