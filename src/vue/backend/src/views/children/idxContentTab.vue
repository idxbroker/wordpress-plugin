<template>
    <div>
        <impress-listings-idx-content
            :updateListings="updateListings"
            :soldListings="soldListings"
            :automaticImport="automaticImport"
            :displayIDXLink="displayIDXLink"
            :defaultListingTemplateSelected="defaultListingTemplateSelected"
            :defaultListingTemplateOptions="defaultListingTemplateOptions"
            :importedListingsAuthorSelected="importedListingsAuthorSelected"
            :importedListingsAuthorOptions="importedListingsAuthorOptions"
            :importTitle="importTitle"
            :advancedFieldData="advancedFieldData"
            :displayAdvancedFields="displayAdvancedFields"
            @form-field-update="setItem($event)"
        ></impress-listings-idx-content>
        <idx-button
            customClass="settings-button__save"
            @click="saveIDXListingsSettings"
        >
            Save
        </idx-button>
    </div>
</template>
<script>
import impressListingsIdxContent from '@/templates/impressListingsIdxContent.vue'
import { mapActions, mapState } from 'vuex'
export default {
    components: { impressListingsIdxContent },
    name: 'listings-idx-content-tab',
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
    }
}
</script>
