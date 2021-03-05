<template>
    <div>
        <impress-listings-advanced-content
            :deregisterMainCss="localStateValues.deregisterMainCss"
            :deregisterWidgetCss="localStateValues.deregisterWidgetCss"
            :sendFormSubmission="localStateValues.sendFormSubmission"
            :formShortcode="localStateValues.formShortcode"
            :googleMapsAPIKey="localStateValues.googleMapsAPIKey"
            :wrapperStart="localStateValues.wrapperStart"
            :wrapperEnd="localStateValues.wrapperEnd"
            :deletePluginDataOnUninstall="localStateValues.deletePluginDataOnUninstall"
            @form-field-update="formUpdate"
        ></impress-listings-advanced-content>
        <idx-button
            customClass="settings-button__save"
            @click="saveAction"
        >
            Save
        </idx-button>
    </div>
</template>
<script>
import impressListingsAdvancedContent from '@/templates/impressListingsAdvancedContent.vue'
import { mapActions, mapState } from 'vuex'
import pageGuard from '@/mixins/pageGuard'
export default {
    name: 'listings-advanced-content-tab',
    mixins: [pageGuard],
    components: {
        impressListingsAdvancedContent
    },
    computed: {
        ...mapState({
            deregisterMainCss: state => state.listingsSettings.deregisterMainCss,
            deregisterWidgetCss: state => state.listingsSettings.deregisterWidgetCss,
            sendFormSubmission: state => state.listingsSettings.sendFormSubmission,
            formShortcode: state => state.listingsSettings.formShortcode,
            googleMapsAPIKey: state => state.listingsSettings.googleMapsAPIKey,
            wrapperStart: state => state.listingsSettings.wrapperStart,
            wrapperEnd: state => state.listingsSettings.wrapperEnd,
            deletePluginDataOnUninstall: state => state.listingsSettings.deletePluginDataOnUninstall
        })
    },
    methods: {
        ...mapActions({
            setItem: 'listingsSettings/setItem',
            saveAdvancedListingsSettings: 'listingsSettings/saveAdvancedListingsSettings'
        })
    },
    created () {
        this.module = 'listingsSettings'
    }
}
</script>
