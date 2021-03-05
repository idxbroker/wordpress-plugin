<template>
    <div>
        <listings-general
            :currencyCodeSelected="currencyCodeSelected"
            :currencySymbolSelected="currencySymbolSelected"
            :defaultDisclaimer="defaultDisclaimer"
            :numberOfPosts="numberOfPosts"
            :listingSlug="listingSlug"
            :defaultState="defaultState"
            @form-field-update="setItem($event)"
        ></listings-general>
        <idx-button
            customClass="settings-button__save"
            @click="saveGeneralListingsSettings"
        >
            Save
        </idx-button>
    </div>
</template>
<script>
import ListingsGeneral from '@/templates/impressListingsGeneralContent'
import { mapState, mapActions } from 'vuex'
export default {
    name: 'listings-general-content-tab',
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
        }),
        newValues () {
            // To Do: Add logic to prevent page load on unsaved form change.
            return false
        }
    },
    beforeRouteLeave (to, from, next) {
        if (this.newValues) {
            const answer = window.confirm('Do you really want to leave? you have unsaved changes!')
            if (answer) {
                next()
            } else {
                next(false)
            }
        } next()
    },
    methods: {
        ...mapActions({
            setItem: 'listingsSettings/setItem',
            saveGeneralListingsSettings: 'listingsSettings/saveGeneralListingsSettings'
        })
    }
}
</script>
