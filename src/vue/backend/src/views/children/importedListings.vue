<template>
    <import-page-template
        action="delete"
        cardType="listings"
        :masterList="listings"
        :description="description"
        :clearSelections="clearSelections"
        @bulk-action="unimportListings"
    ></import-page-template>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import importPageTemplate from '@/templates/importPageTemplate'
export default {
    name: 'imported-listings',
    inject: [PRODUCT_REFS.importContent.repo],
    components: {
        importPageTemplate
    },
    data () {
        return {
            clearSelections: false
        }
    },
    computed: {
        ...mapState({
            listings: state => state.importContent.listings.imported
        })
    },
    methods: {
        async unimportListings (e) {
            this.clearSelections = false
            const postIDs = e.map(x => {
                return x.postId
            })
            const { data } = await this.importContentRepository.delete({ ids: postIDs }, 'listings/delete')
            this.$store.dispatch(`${this.module}/setItem`, { key: 'listings', value: data })
            this.clearSelections = true
        }
    },
    created () {
        this.module = 'importContent'
        this.description = 'Select the imported listings to be deleted from IMPress'
    }
}
</script>
