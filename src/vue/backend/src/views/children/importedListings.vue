<template>
    <div>
        <bulk-action
            action="Delete"
            :selected="selected"
            :description="description"
            :disabled="listingsSelected.length === 0"
            @select-all="selectAll('listing-checkbox', listings)"
            @bulk-action="unimportListings"
        ></bulk-action>
        <idx-block className="listings-card__group">
            <listings-card
                v-for="listing in listings"
                ref="listing-checkbox"
                :key="listing.listingID"
                :listing="listing"
                @listing-selected="updateSelected($event, listingsSelected)"
            >
            </listings-card>
        </idx-block>
    </div>
</template>
<script>
import { mapState } from 'vuex'
import importPages from '@/mixins/importPages'
import BulkAction from '../../components/BulkAction.vue'
import ListingsCard from '../../components/listingsCard.vue'
export default {
    name: 'imported-listings',
    mixins: [importPages],
    components: {
        BulkAction,
        ListingsCard
    },
    data () {
        return {
            listingsSelected: []
        }
    },
    computed: {
        ...mapState({
            listings: state => state.importContent.listings.imported
        })
    },
    methods: {
        unimportListings () {
            // to do: DELETE request with post IDs
        }
    },
    created () {
        this.description = 'Select the imported listings to be deleted from IMPress'
    }
}
</script>
