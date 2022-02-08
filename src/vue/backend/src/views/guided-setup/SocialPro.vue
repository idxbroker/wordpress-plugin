<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="enablePlugin">
        <template v-slot:description>
            <p>
                Add content-rich blog posts to your social media
                platforms and website. Topics include life-style
                content, real estate content and market updates
                for your area.
            </p>
        </template>
        <template v-slot:controls>
            <idx-block v-if="subscribed" className="form-content">
                <idx-form-group>
                    <idx-block
                        :className="{
                            'form-content__toggle': true,
                            'form-content--disabled': formDisabled
                        }"
                    >
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
            <social-pro-upgrade :restrictedByBeta="restrictedByBeta" v-else></social-pro-upgrade>
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { PRODUCT_REFS } from '@/data/productTerms'
import { mapState, mapActions } from 'vuex'
import guidedSetupMixin from '@/mixins/guidedSetup'
import pageGuard from '@/mixins/pageGuard'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
import SocialProUpgrade from '@/components/socialProUpgrade.vue'
export default {
    name: 'guided-setup-social-pro',
    inject: [PRODUCT_REFS.socialPro.repo],
    mixins: [
        guidedSetupMixin,
        pageGuard
    ],
    components: {
        GuidedSetupContentCard,
        SocialProUpgrade
    },
    data () {
        return {
            showError: false,
            formDisabled: false
        }
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.guidedSetup.guidedSetupSteps,
            subscribed: state => state.socialPro.subscribed,
            restrictedByBeta: state => state.socialPro.restrictedByBeta
        }),
        continuePath () {
            return (this.localStateValues.enabled && this.subscribed) ? '/guided-setup/social-pro/configure' : '/guided-setup/confirmation'
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
                const { status } = await this.socialProRepository.post({ enabled: this.localStateValues.enabled }, 'enable')
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
        this.module = 'socialPro'
        this.cardTitle = 'Social Pro'
        this.activateLabel = 'Enable General Interest Article Syndication'
        this.skipPath = '/guided-setup/confirmation'
    },
    mounted () {
        this.progressStepperUpdate([4, 5, 3, 1])
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
