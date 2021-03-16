<template>
    <div>
        <impress-listings-advanced-content
            :formDisabled="formDisabled"
            v-bind="localStateValues"
            @form-field-update="formUpdate"
        ></impress-listings-advanced-content>
        <idx-button
            customClass="settings-button__save"
            @click="saveHandler"
        >
            Save
        </idx-button>
    </div>
</template>
<script>
import { PRODUCT_REFS } from '@/data/productTerms'
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
        this.module = 'listingsSettings'
        const { data } = await this[repo].get('advanced')
        for (const key in data) {
            this.$store.dispatch(`${this.module}/setItem`, { key, value: data[key] })
        }
    }
}
</script>
