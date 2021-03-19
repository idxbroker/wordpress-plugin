<template>
    <GuidedSetupContentCard
        :cardTitle="cardTitle"
        :steps="guidedSetupSteps"
        :relatedLinks="links"
        @back-step="goBackStep"
        @skip-step="goSkipStep"
        @continue="enablePlugin">
        <template v-slot:description>
            <p>A sentence or two about Social Pro and general interest articles. Lorem Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi id sem quis dui ornare fermentum ac non felis.</p>
        </template>
        <template v-slot:controls>
            <p>Enabling Social Pro will:</p>
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
                <idx-block v-if="showError" className="form-content__error">
                    We're experiencing a problem, please try again.
                </idx-block>
            </idx-block>
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import guidedSetupMixin from '@/mixins/guidedSetup'
import pageGuard from '@/mixins/pageGuard'
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
export default {
    name: 'guided-setup-social-pro',
    mixins: [
        guidedSetupMixin,
        pageGuard
    ],
    components: {
        GuidedSetupContentCard
    },
    data () {
        return {
            showError: false,
            formDisabled: false
        }
    },
    computed: {
        ...mapState({
            guidedSetupSteps: state => state.guidedSetup.guidedSetupSteps
        }),
        continuePath () {
            return this.localStateValues.enabled ? '/guided-setup/social-pro/configure' : '/guided-setup/confirmation'
        }
    },
    methods: {
        ...mapActions({
            progressStepperUpdate: 'guidedSetup/progressStepperUpdate'
        }),
        async enablePlugin () {
            this.showError = false
            this.formDisabled = true
            // There is no endpoint, so this is to-do
            if (this.formIsUpdated) {
                const status = 204 // endpoint call here
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
        this.cardTitle = 'Auto Publish & Syndicate with Social Pro'
        this.activateLabel = 'Enable General Interest Article Syndication'
        this.skipPath = '/guided-setup/confirmation'
        this.links = [
            {
                text: 'Social Pro with IDX Broker',
                href: '#social-pro'
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
