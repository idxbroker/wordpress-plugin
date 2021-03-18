<template>
    <section>
        <template v-if="!enabled || !isValid">
            <feedback
                :title="title"
                :link="link"
                :content="content"
                :missingAPI="!isValid"
            >
            </feedback>
        </template>
        <template v-else>
            <h2>Import IDX Listings</h2>
            <Tabbed v-bind="$props" />
        </template>
    </section>
</template>
<script>
import { mapState } from 'vuex'
import TabbedMixin from '@/mixins/Tabbed'
import Tabbed from '@/templates/layout/Tabbed'
import Feedback from '@/components/importFeedback.vue'
export default {
    mixins: [TabbedMixin],
    components: {
        Tabbed,
        Feedback
    },
    computed: {
        ...mapState({
            enabled: state => state.listingsSettings.enabled,
            isValid: state => state.general.isValid
        }),
        title () {
            return this.isValid ? 'Impress Listings is Disabled' : 'API Key Required'
        },
        link () {
            return '/settings/listings'
        },
        content () {
            return {
                startingStatement: 'To import your listings, you need to',
                warningLink: 'enable IMPress Listings',
                closingStatement: 'and ensure that your API key is active'
            }
        }
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/tabContainer';
</style>
