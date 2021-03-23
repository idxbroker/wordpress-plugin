<template>
    <TwoColumn title="IMPress Listings Settings">
        <idx-block className="form-content__toggle">
            Enable
            <idx-toggle-slider
                uncheckedState="No"
                checkedState="Yes"
                label="Enable IMPress Listings"
                :active="localStateValues.enabled"
                :disabled="formDisabled"
                @toggle="enablePlugin"
            ></idx-toggle-slider>
        </idx-block>
        <template #related>
            <related-links
                :relatedLinks="links"
            ></related-links>
        </template>
        <Tabbed
            v-bind="$props"
            v-show="enabled"
        />
    </TwoColumn>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import pageGuard from '@/mixins/pageGuard'
import TwoColumn from '@/templates/layout/TwoColumn'
import TabbedMixin from '@/mixins/Tabbed'
import standaloneSettingsActions from '@/mixins/standaloneSettingsActions'
import Tabbed from '@/templates/layout/Tabbed'
import RelatedLinks from '../../components/RelatedLinks.vue'
const { listingsSettings: { repo } } = PRODUCT_REFS
export default {
    inject: [repo],
    mixins: [TabbedMixin, pageGuard, standaloneSettingsActions],
    components: {
        Tabbed,
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
            enabled: state => state.listingsGeneral.enabled
        })
    },
    methods: {
        enablePlugin () {
            this.enablePluginAction(this[repo])
        }
    },
    async created () {
        this.module = 'listingsGeneral'
        this.links = [
            { text: 'Where can I find my API key?', href: 'https://support.idxbroker.com/s/article/api-key' },
            { text: 'IDX Broker Middleware', href: 'https://middleware.idxbroker.com/mgmt/' },
            { text: 'Sign up for IDX Broker', href: 'https://signup.idxbroker.com/' } // Marketing may want a different entry
        ]
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/tabContainer';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider';
</style>
