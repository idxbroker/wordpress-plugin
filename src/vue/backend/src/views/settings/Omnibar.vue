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
                @click="saveHandler"
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
import OmnibarForm from '@/templates/omnibarForm.vue'
import RelatedLinks from '@/components/RelatedLinks.vue'
export default {
    name: 'standalone-omnibar-settings',
    inject: [PRODUCT_REFS.omnibar.repo],
    mixins: [pageGuard, omnibarMixin],
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
        async saveHandler () {
            // To Do: user facing error checking
            if (this.formIsUpdated) {
                this.formDisabled = true
                try {
                    await this.omnibarRepository.post(this.localStateValues)
                    this.formDisabled = false
                    this.saveAction()
                } catch (error) {
                    this.formDisabled = false
                    if (error.response.status === 401) {
                    } else {
                        this.errorAction()
                    }
                }
            } else {
                this.saveAction()
            }
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
        this.formDisabled = true
        const { data } = await this.omnibarRepository.get()
        for (const key in data) {
            this.$store.dispatch(`${this.module}/setItem`, { key, value: data[key] })
        }
        this.formDisabled = false
    }
}
</script>
