<template>
    <div>
        <listings-general
            v-bind="localStateValues"
            @form-field-update="formUpdate"
        ></listings-general>
        <idx-button
            customClass="settings-button__save"
            @click="saveAction"
        >
            Save
        </idx-button>
    </div>
</template>
<script>
import ListingsGeneral from '@/templates/impressListingsGeneralContent'
import { mapState, mapActions } from 'vuex'
import pageGuard from '@/mixins/pageGuard'
export default {
    name: 'listings-general-content-tab',
    mixins: [pageGuard],
    components: {
        ListingsGeneral
    },
    computed: {
        ...mapState({
            currencyCodeSelected: state => state.listingsSettings.currencyCodeSelected,
            currencySymbolSelected: state => state.listingsSettings.currencySymbolSelected,
            defaultDisclaimer: state => state.listingsSettings.defaultDisclaimer,
            numberOfPosts: state => state.listingsSettings.numberOfPosts,
            listingSlug: state => state.listingsSettings.listingSlug,
            defaultState: state => state.listingsSettings.defaultState
        })
    },
    methods: {
        ...mapActions({
            setItem: 'listingsSettings/setItem',
            saveGeneralListingsSettings: 'listingsSettings/saveGeneralListingsSettings'
        })
    },
    created () {
        this.module = 'listingsSettings'
    }
}
</script>
