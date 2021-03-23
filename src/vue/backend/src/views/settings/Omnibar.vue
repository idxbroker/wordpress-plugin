<template>
    <TwoColumn title="Omnibar Settings">
        <idx-block className="form-content">
            <omnibar-form
                :formDisabled="formDisabled"
                v-bind="localStateValues"
                @form-field-update="formUpdate"
                @form-field-update-mls-membership="mlsChangeUpdate"
            ></omnibar-form>
            <idx-button
                size="lg"
                @click="save"
            >
                Save
            </idx-button>
        </idx-block>
        <template #related>
            <RelatedLinks :relatedLinks="relatedLinks"/>
        </template>
    </TwoColumn>
</template>
<script>
import { PRODUCT_REFS } from '@/data/productTerms'
import TwoColumn from '@/templates/layout/TwoColumn'
import pageGuard from '@/mixins/pageGuard'
import omnibarMixin from '@/mixins/omnibarMixin'
import standaloneSettingsActions from '@/mixins/standaloneSettingsActions'
import OmnibarForm from '@/templates/omnibarForm.vue'
import RelatedLinks from '@/components/RelatedLinks.vue'
const { omnibar: { repo } } = PRODUCT_REFS
export default {
    name: 'standalone-omnibar-settings',
    inject: [repo],
    mixins: [pageGuard, omnibarMixin, standaloneSettingsActions],
    components: {
        TwoColumn,
        OmnibarForm,
        RelatedLinks
    },
    data () {
        return {
            formDisabled: false
        }
    },
    methods: {
        save () {
            this.saveHandler(this[repo], '', this.localStateValues)
        }
    },
    async created () {
        this.module = 'omnibar'
        this.relatedLinks = [
            {
                text: 'IMPress Omnibar FAQs',
                href: '#'
            },
            {
                text: 'Setting up a wrapper',
                href: '#wrapper'
            },
            {
                text: 'IDX Broker Middleware',
                href: 'https://middleware.idxbroker.com/mgmt/'
            },
            {
                text: 'Sign up for IDX Broker',
                href: '#signUp'
            }
        ]
        this.loadData(this[repo])
    }
}
</script>
