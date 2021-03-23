<template>
    <div>
        <listings-general
            :formDisabled="formDisabled"
            v-bind="localStateValues"
            @form-field-update="formUpdate"
        ></listings-general>
        <idx-button
            size="lg"
            @click="save"
        >
            Save
        </idx-button>
    </div>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import ListingsGeneral from '@/templates/impressListingsGeneralContent'
import pageGuard from '@/mixins/pageGuard'
import standaloneSettingsActions from '@/mixins/standaloneSettingsActions'
const { listingsSettings: { repo } } = PRODUCT_REFS
export default {
    name: 'listings-general-content-tab',
    inject: [repo],
    mixins: [pageGuard, standaloneSettingsActions],
    inheritAttrs: false,
    components: {
        ListingsGeneral
    },
    computed: {
        ...mapState({
            enabled: state => state.listingsGeneral.enabled
        })
    },
    methods: {
        save () {
            this.saveHandler(this[repo], 'general')
        }
    },
    created () {
        this.module = 'listingsGeneral'
        if (this.enabled) {
            this.loadData(this[repo], 'general')
        }
    }
}
</script>
