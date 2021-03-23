<template>
    <TwoColumn title="Social Pro Syndication Settings">
        <idx-block className="form-content">
            <div>
                <b>Social Pro</b>
                <div>Detailed sentence or two describing Social Pro General Interest Articles. Lorem ipsum dolor sit amet.</div>
                <idx-block className="form-content__toggle">
                    {{ toggleLabel }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        :active="enabled"
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
        TwoColumn,
        RelatedLinks
    },
    data () {
        return {
            formDisabled: false
        }
    },
    computed: {
        ...mapState({
            enabled: state => state.socialPro.enabled
        })
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
        if (this.enabled) {
            this.loadData(this[repo])
        }
    }
}
</script>
