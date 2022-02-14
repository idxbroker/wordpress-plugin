<template>
    <import-page-template
        cardType="listings"
        :masterList="listings"
        :description="description"
        :clearSelections="clearSelections"
        :loading="loading"
        @bulk-action="importItems($event, 'listings')"
    ></import-page-template>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import importActions from '@/mixins/importActions'
import importPageTemplate from '@/templates/importPageTemplate'
export default {
    name: 'unimported-listings',
    inject: [PRODUCT_REFS.importContent.repo],
    mixins: [importActions],
    components: {
        importPageTemplate
    },
    computed: {
        ...mapState({
            listings: state => state.importContent.listings.unimported
        })
    },
    created () {
        this.description = 'Select listings to import from IDX Broker to IMPress. Please note that it may take up to 15 minutes to complete your import, depending on file size.'
    }
}
</script>
