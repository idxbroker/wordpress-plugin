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
                @toggle="refreshPage"
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
import { PRODUCT_REFS } from '@/data/productTerms'
import pageGuard from '@/mixins/pageGuard'
import TwoColumn from '@/templates/layout/TwoColumn'
import TabbedMixin from '@/mixins/Tabbed'
import Tabbed from '@/templates/layout/Tabbed'
import RelatedLinks from '../../components/RelatedLinks.vue'
import { mapState } from 'vuex'
export default {
    inject: [PRODUCT_REFS.listingsSettings.repo],
    mixins: [TabbedMixin, pageGuard],
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
        async refreshPage () {
            this.formDisabled = true
            this.formUpdate({ key: 'enabled', value: !this.enabled })
            const { status } = await this.listingsSettingsRepository.post({ enabled: !this.enabled }, 'enable')
            this.formDisabled = false
            if (status === (204 || 200)) {
                location.reload()
            } else {
                this.errorAction()
            }
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
