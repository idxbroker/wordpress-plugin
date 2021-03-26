<template>
    <idx-block className="section" v-if="!subscribed">
        <h1>Social Pro</h1>
        <social-pro-upgrade></social-pro-upgrade>
    </idx-block>
    <idx-block className="section" v-else-if="!isValid">
        <feedback
            :title="title"
            :missingAPI="!isValid"
            :content="content"
        >
        </feedback>
    </idx-block>
    <TwoColumn v-else title="Social Pro Syndication Settings">
        <idx-block className="form-content">
            <div>
                <b>Social Pro</b>
                <div>Detailed sentence or two describing Social Pro General Interest Articles. Lorem ipsum dolor sit amet.</div>
                <idx-block
                    :className="{
                        'form-content__toggle': true,
                        'form-content--disabled': formDisabled
                    }"
                >
                    {{ toggleLabel }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        :active="localStateValues.enabled"
                        :disabled="formDisabled"
                        :label="toggleLabel"
                        @toggle="enablePlugin"
                    ></idx-toggle-slider>
                </idx-block>
            </div>
            <div v-if="enabled">
                <SocialProForm
                    :formDisabled="formDisabled"
                    v-bind="localStateValues"
                    @form-field-update="formUpdate"
                />
                <idx-button
                    size="lg"
                    @click="save"
                >
                    Save
                </idx-button>
            </div>
        </idx-block>
        <template #related>
            <RelatedLinks :relatedLinks="relatedLinks"/>
        </template>
    </TwoColumn>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import SocialProForm from '@/templates/socialProForm'
import RelatedLinks from '@/components/RelatedLinks.vue'
import Feedback from '@/components/importFeedback.vue'
import socialProUpgrade from '@/components/socialProUpgrade.vue'
import TwoColumn from '@/templates/layout/TwoColumn'
import pageGuard from '@/mixins/pageGuard'
import standaloneSettingsActions from '@/mixins/standaloneSettingsActions'
const { socialPro: { repo } } = PRODUCT_REFS
export default {
    name: 'standalone-social-pro-settings',
    inject: [repo],
    mixins: [pageGuard, standaloneSettingsActions],
    components: {
        SocialProForm,
        socialProUpgrade,
        TwoColumn,
        RelatedLinks,
        Feedback
    },
    data () {
        return {
            formDisabled: false
        }
    },
    computed: {
        ...mapState({
            subscribed: state => state.socialPro.subscribed,
            isValid: state => state.general.isValid,
            enabled: state => state.socialPro.enabled
        })
    },
    watch: {
        enabled () {
            if (this.enabled && this.subscribed && this.isValid) {
                console.log('load')
                this.loadData(this[repo])
            }
        }
    },
    methods: {
        enablePlugin () {
            this.enablePluginAction(this[repo])
        },
        save () {
            this.saveHandler(this[repo])
        }
    },
    created () {
        this.module = 'socialPro'
        this.toggleLabel = 'Enable General Interest Article Syndication'
        this.title = 'API Key Required'
        this.content = {
            startingStatement: 'To use Social Pro, you need to'
        }
        this.relatedLinks = [
            {
                text: 'Social Pro with IDX Broker',
                href: '#'
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
}
</script>
