<template>
    <import-page-template
        cardType="listings"
        :masterList="listings"
        :description="description"
        :clearSelections="clearSelections"
        @bulk-action="importListings"
    ></import-page-template>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import importPageTemplate from '@/templates/importPageTemplate'
export default {
    name: 'unimported-listings',
    inject: [PRODUCT_REFS.importContent.repo],
    components: {
        importPageTemplate
    },
    data () {
        return {
            checkProgress: '',
            clearSelections: false
        }
    },
    computed: {
        ...mapState({
            listings: state => state.importContent.listings.unimported
        })
    },
    methods: {
        async importListings (e) {
            this.clearSelections = false
            const listingIds = e.map(x => {
                return x.listingId
            })
            try {
                await this.importContentRepository.post({ ids: listingIds }, 'listings/import')
                let { data } = await this.importContentRepository.get('listings')
                if (data.inProgress) {
                    this.checkProgress = setInterval(async () => {
                        if (data.inProgress) {
                            const check = await this.importContentRepository.get('listings')
                            data = check.data
                            this.clearSelections = true
                        } else {
                            clearInterval(this.checkProgress)
                            this.$store.dispatch('importContent/setItem', { key: 'listings', value: data })
                        }
                    }, 5000)
                } else {
                    this.clearSelections = true
                    this.$store.dispatch('importContent/setItem', { key: 'listings', value: data })
                }
            } catch (error) {
                // To-do: error banner
            }
        }
    },
    created () {
        this.description = 'Select listings to import from IDX Broker to IMPress'
    }
}
</script>
