<template>
    <import-page-template
        action="delete"
        cardType="listings"
        :masterList="listings"
        :description="description"
        @bulk-action="unimportListings"
    ></import-page-template>
</template>
<script>
import { mapState } from 'vuex'
import importPageTemplate from '@/templates/importPageTemplate'
export default {
    name: 'imported-listings',
    components: {
        importPageTemplate
    },
    computed: {
        ...mapState({
            listings: state => state.importContent.listings.imported
        })
    },
    methods: {
        unimportListings (e) {
            // to do: DELETE request with post IDs
        }
    },
    created () {
        this.description = 'Select the imported listings to be deleted from IMPress'
    }
}
</script>
