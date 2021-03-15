<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="saveHandler">
        <template v-slot:description>
            <p><strong>This is optional.</strong> A sentence or two about why you should install IMPress Listings to your WordPress site. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </template>
        <template v-slot:controls>
            <p>Activating IMPress Agents will:</p>
            <idx-list customClass="list-featured">
                <idx-list-item>Add Feature 1</idx-list-item>
                <idx-list-item>Enable Feature 2</idx-list-item>
                <idx-list-item>Import Feature 3</idx-list-item>
                <idx-list-item>Automate Feature 4</idx-list-item>
            </idx-list>
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
            </idx-block>
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { PRODUCT_REFS } from '@/data/productTerms'
import { mapState, mapActions } from 'vuex'
import guidedSetupMixin from '@/mixins/guidedSetup'
import pageGuard from '@/mixins/pageGuard'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    inject: [PRODUCT_REFS.agentSettings.repo],
    name: 'guided-setup-agents',
    mixins: [
        guidedSetupMixin,
        pageGuard
    ],
    components: {
        GuidedSetupContentCard
    },
    data () {
        return {
            formDisabled: true
        }
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.progressStepper.guidedSetupSteps
        }),
        continuePath () {
            return this.localStateValues.enabled ? '/guided-setup/agents/configure' : this.skipPath
        }
    },
    methods: {
        ...mapActions({
            progressStepperUpdate: 'progressStepper/progressStepperUpdate'
        }),
        async saveHandler () {
            this.formDisabled = true
            if (this.formChanges) {
                const { status } = await this.agentSettingsRepository.post({ enabled: this.localStateValues.enabled }, 'enable')
                if (status === 204) {
                    this.saveAction()
                    this.$router.push({ path: this.continuePath })
                } else {
                    this.formDisabled = false
                    // To do: user feedback
                }
            } else {
                this.$router.push({ path: this.continuePath })
            }
        }
    },
    created () {
        this.module = 'agentSettings'
        this.cardTitle = 'Enable IMPress Agents'
        this.activateLabel = 'Enable'
        this.skipPath = '/guided-setup/social-pro'
        this.links = [
            {
                text: 'IMPress Agents Features',
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
        this.formDisabled = false
        this.progressStepperUpdate([4, 5, 1, 0])
    }
}
</script>

<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider.scss';
@import '@/styles/formContentStyles.scss';
.list-featured {
    column-count: 2;
    font-weight: 600;
    list-style-type: circle;
    padding-left: 1.125em;
}
</style>
