<template>
    <TwoColumn title="IMPress Listings Settings">
        <idx-block className="form-content__toggle">
            Activate
            <idx-toggle-slider
                uncheckedState="No"
                checkedState="Yes"
                @toggle="refreshPage"
                :active="enabled"
                label="Active IMPress Listings"
            ></idx-toggle-slider>
        </idx-block>
        <template #related>
            <related-links
                :relatedLinks="links"
            ></related-links>
        </template>
        <Tabbed v-bind="$props" v-show="enabled"/>
        <idx-button
            v-if="enabled"
            customClass="settings-button__save"
            @click="saveListingsSettings"
        >
            Save
        </idx-button>
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
    data () {
        return {
            links: [
                { text: 'Where can I find my API key?', href: 'https://support.idxbroker.com/s/article/api-key' },
                { text: 'IDX Broker Middleware', href: 'https://middleware.idxbroker.com/mgmt/' },
                { text: 'Sign up for IDX Broker', href: 'https://signup.idxbroker.com/' } // Marketing may want a different entry
            ]
        }
    },
    beforeRouteUpdate (to, from, next) {
        if (this.formUpdated) {
            const answer = window.confirm('Do you really want to leave? you have unsaved changes!')
            if (answer) {
                next()
            } else {
                next(false)
            }
        } next()
    },
    computed: {
        ...mapState({
            enabled: state => state.listingsSettings.enabled
        }),
        formUpdated () {
            // This will require the overhaul of component forms to decouple state changes
            /* Listen for changes made on the state, and if the new
            value is different than the old one, it will be added to an array. The form will be considered updated
            and will trigger the response in beforeRouteUpdate */
            return false
        }
    },
    methods: {
        ...mapActions({
            listingsSettingsStateChange: 'listingsSettings/listingsSettingsStateChange',
            saveListingsSettings: 'listingsSettings/saveListingsSettings'
        }),
        async refreshPage () {
            await this.listingsSettingsStateChange({ key: 'enabled', value: !this.enabled })
            location.reload()
        }
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/tabContainer';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider';
@import '../../styles/formContentStyles.scss';
.settings-button__save {
    width: 155px;
    height: 50px;
    margin-top: 40px;
}
</style>
