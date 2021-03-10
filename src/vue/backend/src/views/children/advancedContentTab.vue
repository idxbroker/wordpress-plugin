<template>
    <div>
        <impress-listings-advanced-content
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
import { mapState } from 'vuex'
import pageGuard from '@/mixins/pageGuard'
const { listingsSettings: { repo } } = PRODUCT_REFS
export default {
    name: 'listings-advanced-content-tab',
    inject: [repo],
    mixins: [pageGuard],
    components: {
        impressListingsAdvancedContent
    },
    computed: {
        ...mapState({
            advancedSettings: state => state.listingsSettings
        })
    },
    methods: {
        async saveHandler () {
            const { status } = await this[repo].post(this.formChanges, 'advanced')
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
