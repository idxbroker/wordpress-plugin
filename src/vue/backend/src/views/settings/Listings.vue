<template>
    <TwoColumn title="IMPress Listings Settings">
        <idx-block className="form-content__toggle">
            Enable
            <idx-toggle-slider
                uncheckedState="No"
                checkedState="Yes"
                @toggle="refreshPage"
                :active="enabled"
                label="Enable IMPress Listings"
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
import TwoColumn from '@/templates/layout/TwoColumn'
import TabbedMixin from '@/mixins/Tabbed'
import Tabbed from '@/templates/layout/Tabbed'
import RelatedLinks from '../../components/RelatedLinks.vue'
import { mapActions, mapState } from 'vuex'
export default {
    mixins: [TabbedMixin],
    components: {
        Tabbed,
        TwoColumn,
        RelatedLinks
    },
    computed: {
        ...mapState({
            enabled: state => state.listingsSettings.enabled
        })
    },
    methods: {
        ...mapActions({
            setItem: 'listingsSettings/setItem'
        }),
        async refreshPage () {
            await this.setItem({ key: 'enabled', value: !this.enabled })
            location.reload()
        }
    },
    created () {
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
