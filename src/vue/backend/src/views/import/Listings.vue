<template>
    <idx-block className="section">
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
    </idx-block>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import TabbedMixin from '@/mixins/Tabbed'
import Tabbed from '@/templates/layout/Tabbed'
import Feedback from '@/components/importFeedback.vue'
export default {
    name: 'import-idx-listings-container',
    inject: [PRODUCT_REFS.importContent.repo],
    mixins: [TabbedMixin],
    components: {
        Tabbed,
        Feedback
    },
    computed: {
        ...mapState({
            enabled: state => state.listingsGeneral.enabled,
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
    },
    async created () {
        this.module = 'importContent'
        this.description = 'Select the imported listings to be deleted from IMPress'
        const { data } = await this.importContentRepository.get('listings')
        this.$store.dispatch(`${this.module}/setItem`, { key: 'listings', value: data })
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/tabContainer';
</style>
