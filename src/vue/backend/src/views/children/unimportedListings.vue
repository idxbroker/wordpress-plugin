<template>
    <div>
        <bulk-action
            :selected="selected"
            :description="description"
            :disabled="listingsSelected.length === 0"
            @select-all="selectAll('listing-checkbox', listings)"
            @bulk-action="importListings"
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
    name: 'unimported-listings',
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
            listings: state => state.importContent.listings.unimported
        })
    },
    methods: {
        importListings () {
            // to do: POST request listing IDs to be imported
        }
    },
    created () {
        this.description = 'Select listings to import from IDX Broker to IMPress'
    }
}
</script>
