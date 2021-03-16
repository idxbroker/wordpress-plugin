<template>
    <div>
        <impress-listings-idx-content
            v-bind="localStateValues"
            @form-field-update="formUpdate"
        ></impress-listings-idx-content>
        <idx-button
            customClass="settings-button__save"
            size="lg"
            @click="saveHandler"            
        >
            Save
        </idx-button>
    </div>
</template>
<script>
import { PRODUCT_REFS } from '@/data/productTerms'
import impressListingsIdxContent from '@/templates/impressListingsIdxContent.vue'
import pageGuard from '@/mixins/pageGuard'
export default {
    name: 'listings-idx-content-tab',
    inject: [PRODUCT_REFS.listingsSettings.repo],
    mixins: [pageGuard],
    inheritAttrs: false,
    components: {
        impressListingsIdxContent
    },
    methods: {
        async saveHandler () {
            const { status } = await this.listingsSettingsRepository.post(this.formChanges, 'idx')
            if (status === 204) {
                this.saveAction()
                // To Do: User feed back
            }
        }
    },
    async created () {
        this.module = 'listingsSettings'
        const { data } = await this.listingsSettingsRepository.get('idx')
        this.updateState(data)
    }
}
</script>
