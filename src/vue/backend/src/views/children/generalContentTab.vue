<template>
    <div>
        <listings-general
            :formDisabled="formDisabled"
            v-bind="localStateValues"
            @form-field-update="formUpdate"
        ></listings-general>
        <idx-button
            size="lg"
            @click="saveHandler"
        >
            Save
        </idx-button>
    </div>
</template>
<script>
import { mapState } from 'vuex'
import ListingsGeneral from '@/templates/impressListingsGeneralContent'
import pageGuard from '@/mixins/pageGuard'
import { PRODUCT_REFS } from '@/data/productTerms'
export default {
    name: 'listings-general-content-tab',
    inject: [PRODUCT_REFS.listingsSettings.repo],
    mixins: [pageGuard],
    inheritAttrs: false,
    components: {
        ListingsGeneral
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
            const { status } = await this.listingsSettingsRepository.post(this.formChanges, 'general')
            this.formDisabled = false
            if (status === 204) {
                this.saveAction()
            } else {
                this.errorAction()
            }
        }
    },
    async created () {
        this.module = 'listingsGeneral'
        if (this.enabled) {
            const { data } = await this.listingsSettingsRepository.get('general')
            this.updateState(data)
        }
    }
}
</script>
