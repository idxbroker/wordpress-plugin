<template>
    <div>
        <impress-listings-idx-content
            :formDisabled="formDisabled"
            v-bind="localStateValues"
            @form-field-update="formUpdate"
        ></impress-listings-idx-content>
        <idx-button
            customClass="settings-button__save"
            @click="saveAction"
        >
            Save
        </idx-button>
    </div>
</template>
<script>
import impressListingsIdxContent from '@/templates/impressListingsIdxContent.vue'
import pageGuard from '@/mixins/pageGuard'
import { mapActions, mapState } from 'vuex'
export default {
    name: 'listings-idx-content-tab',
    mixins: [pageGuard],
    components: { impressListingsIdxContent },
    data () {
        return {
            formDisabled: false
        }
    },
    computed: {
        ...mapState({
            updateListings: state => state.listingsSettings.updateListings,
            soldListings: state => state.listingsSettings.soldListings,
            automaticImport: state => state.listingsSettings.automaticImport,
            displayIDXLink: state => state.listingsSettings.displayIDXLink,
            defaultListingTemplateSelected: state => state.listingsSettings.defaultListingTemplateSelected,
            defaultListingTemplateOptions: state => state.listingsSettings.defaultListingTemplateOptions,
            importedListingsAuthorSelected: state => state.listingsSettings.importedListingsAuthorSelected,
            importedListingsAuthorOptions: state => state.listingsSettings.importedListingsAuthorOptions,
            importTitle: state => state.listingsSettings.importTitle,
            advancedFieldData: state => state.listingsSettings.advancedFieldData,
            displayAdvancedFields: state => state.listingsSettings.displayAdvancedFields
        })
    },
    methods: {
        ...mapActions({
            setItem: 'listingsSettings/setItem',
            saveIDXListingsSettings: 'listingsSettings/saveIDXListingsSettings'
        })
    },
    created () {
        this.module = 'listingsSettings'
    }
}
</script>
