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
            @click="save"
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
import standaloneSettingsActions from '@/mixins/standaloneSettingsActions'
const { listingsSettings: { repo } } = PRODUCT_REFS
export default {
    name: 'listings-idx-content-tab',
    inject: [repo],
    mixins: [pageGuard, standaloneSettingsActions],
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
    watch: {
        enabled () {
            this.loadData(this[repo], 'idx')
        }
    },
    methods: {
        save () {
            this.saveHandler(this[repo], 'idx')
        }
    },
    created () {
        this.module = 'listingsIdx'
        if (this.enabled) {
            this.loadData(this[repo], 'idx')
        }
    }
}
</script>
