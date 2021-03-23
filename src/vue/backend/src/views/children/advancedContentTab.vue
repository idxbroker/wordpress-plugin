<template>
    <div>
        <impress-listings-advanced-content
            :formDisabled="formDisabled"
            v-bind="localStateValues"
            @form-field-update="formUpdate"
        ></impress-listings-advanced-content>
        <idx-button
            size="lg"
            @click="save"
        >
            Save
        </idx-button>
    </div>
</template>
<script>
import { PRODUCT_REFS } from '@/data/productTerms'
import { mapState } from 'vuex'
import impressListingsAdvancedContent from '@/templates/impressListingsAdvancedContent.vue'
import pageGuard from '@/mixins/pageGuard'
import standaloneSettingsActions from '@/mixins/standaloneSettingsActions'
const { listingsSettings: { repo } } = PRODUCT_REFS
export default {
    name: 'listings-advanced-content-tab',
    inject: [repo],
    mixins: [pageGuard, standaloneSettingsActions],
    components: {
        impressListingsAdvancedContent
    },
    data () {
        return {
            formDisabled: false
        }
    },
    methods: {
        save () {
            this.saveHandler(this[repo], 'advanced')
        }
    },
    computed: {
        ...mapState({
            enabled: state => state.listingsGeneral.enabled
        })
    },
    async created () {
        this.module = 'listingsAdvanced'
        if (this.enabled) {
            this.loadData(this[repo], 'advanced')
        }
    }
}
</script>
