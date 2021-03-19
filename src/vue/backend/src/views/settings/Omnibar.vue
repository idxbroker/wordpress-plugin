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
import TwoColumn from '@/templates/layout/TwoColumn'
import pageGuard from '@/mixins/pageGuard'
import omnibarMixin from '@/mixins/omnibarMixin'
import OmnibarForm from '@/templates/omnibarForm.vue'
import RelatedLinks from '@/components/RelatedLinks.vue'
export default {
    name: 'omnibar',
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
        saveHandler () {
            this.formDisabled = true
            if (this.formChanges) {
                this.formDisabled = false
                if (status === 204) {
                    this.saveAction()
                } else {
                    // To do: user feedback
                    this.errorAction()
                }
            }
        }
    },
    created () {
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
    }
}
</script>
