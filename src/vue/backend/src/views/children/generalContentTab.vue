<template>
    <div>
        <listings-general
            :formDisabled="formDisabled"
            v-bind="localStateValues"
            @form-field-update="formUpdate"
        ></listings-general>
        <idx-button
            customClass="settings-button__save"
            @click="saveHandler"
        >
            Save
        </idx-button>
    </div>
</template>
<script>
import ListingsGeneral from '@/templates/impressListingsGeneralContent'
import pageGuard from '@/mixins/pageGuard'
import { PRODUCT_REFS } from '@/data/productTerms'
export default {
    name: 'listings-general-content-tab',
    inject: [PRODUCT_REFS.listingsSettings.repo],
    mixins: [pageGuard],
    components: {
        ListingsGeneral
    },
    data () {
        return {
            formDisabled: false
        }
    },
    methods: {
        async saveHandler () {
            this.formDisabled = true
            const { status } = await this.listingsSettingsRepository.post(this.formChanges, 'general')
            this.formDisabled = false
            if (status === 204) {
                this.saveAction()
                // To Do: User feed back
            }
        }
    },
    async created () {
        this.module = 'listingsSettings'
        const { data } = await this.listingsSettingsRepository.get('general')
        this.updateState(data)
    }
}
</script>
