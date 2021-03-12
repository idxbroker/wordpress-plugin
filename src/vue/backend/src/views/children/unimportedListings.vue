<template>
    <import-page-template
        action="delete"
        cardType="listings"
        :masterList="listings"
        :description="description"
        @bulk-action="importListings"
    ></import-page-template>
</template>
<script>
import { mapState } from 'vuex'
import importPageTemplate from '@/templates/importPageTemplate'
export default {
    name: 'unimported-listings',
    components: {
        importPageTemplate
    },
    computed: {
        ...mapState({
            listings: state => state.importContent.listings.unimported
        })
    },
    methods: {
        importListings (e) {
            // to do: POST request listing IDs to be imported
        }
    },
    created () {
        this.description = 'Select listings to import from IDX Broker to IMPress'
    }
}
</script>
