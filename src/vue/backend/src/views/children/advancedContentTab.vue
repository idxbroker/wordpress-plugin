<template>
    <div>
        <impress-listings-advanced-content
            :deregisterMainCss="deregisterMainCss"
            :deregisterWidgetCss="deregisterWidgetCss"
            :sendFormSubmission="sendFormSubmission"
            :formShortcode="formShortcode"
            :googleMapsAPIKey="googleMapsAPIKey"
            :wrapperStart="wrapperStart"
            :wrapperEnd="wrapperEnd"
            :deletePluginDataOnUninstall="deletePluginDataOnUninstall"
            @form-field-update="setItem($event)"
        ></impress-listings-advanced-content>
        <idx-button
            customClass="settings-button__save"
            @click="saveAdvancedListingsSettings"
        >
            Save
        </idx-button>
    </div>
</template>
<script>
import impressListingsAdvancedContent from '@/templates/impressListingsAdvancedContent.vue'
import { mapActions, mapState } from 'vuex'
export default {
    name: 'listings-advanced-content-tab',
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
    }
}
</script>
