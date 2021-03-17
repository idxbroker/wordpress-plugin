<template>
    <div>
        <impress-listings-advanced-content
            :formDisabled="formDisabled"
            v-bind="localStateValues"
            @form-field-update="formUpdate"
        ></impress-listings-advanced-content>
        <idx-button
            size="lg"
            @click="saveHandler"
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
const { listingsSettings: { repo } } = PRODUCT_REFS
export default {
    name: 'listings-advanced-content-tab',
    inject: [repo],
    mixins: [pageGuard],
    components: {
        impressListingsAdvancedContent
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
            const { status } = await this[repo].post(this.formChanges, 'advanced')
            this.formDisabled = false
            if (status === 200) {
                this.saveAction()
            }
        }
    },
    async created () {
        this.module = 'listingsAdvanced'
        if (this.enabled) {
            const { data } = await this[repo].get('advanced')
            this.updateState(data)
        }
    }
}
</script>
