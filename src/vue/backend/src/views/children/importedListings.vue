<template>
    <import-page-template
        action="delete"
        cardType="listings"
        :masterList="listings"
        :description="description"
        :clearSelections="clearSelections"
        @bulk-action="unimportItems($event, 'listings')"
    ></import-page-template>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import importActions from '@/mixins/importActions'
import importPageTemplate from '@/templates/importPageTemplate'
export default {
    name: 'imported-listings',
    inject: [PRODUCT_REFS.importContent.repo],
    mixins: [importActions],
    components: {
        importPageTemplate
    },
    computed: {
        ...mapState({
            listings: state => state.importContent.listings.imported
        })
    },
    created () {
        this.description = 'Select the imported listings to be deleted from IMPress'
    }
}
</script>
