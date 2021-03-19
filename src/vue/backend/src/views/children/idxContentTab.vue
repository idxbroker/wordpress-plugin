<template>
    <div>
        <impress-listings-idx-content
            :formDisabled="formDisabled"
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
import { mapState } from 'vuex'
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
    data () {
        return {
            formDisabled: false
        }
    },
    computed: {
        ...mapState({
            enabled: state => state.listingsGeneral.enabled
        })
    },
    methods: {
        async saveHandler () {
            this.formDisabled = true
            const { status } = await this.listingsSettingsRepository.post(this.formChanges, 'idx')
            this.formDisabled = false
            if (status === 204) {
                this.saveAction()
                // To Do: User feed back
            }
        }
    },
    async created () {
        this.module = 'listingsIdx'
        if (this.enabled) {
            const { data } = await this.listingsSettingsRepository.get('idx')
            this.updateState(data)
        }
    }
}
</script>
