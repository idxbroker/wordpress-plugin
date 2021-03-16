<template>
    <TwoColumn title="Omnibar Settings">
        <idx-block className="form-content">
            <omnibar-form
                :formDisabled="formDisabled"
                v-bind="localStateValues"
                @form-field-update="formUpdate"
            ></omnibar-form>
            <idx-button
                customClass="settings-button__save"
                size="lg"
                @click="saveAction"
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
import { mapActions } from 'vuex'
import TwoColumn from '@/templates/layout/TwoColumn'
import pageGuard from '@/mixins/pageGuard'
import OmnibarForm from '@/templates/omnibarForm.vue'
import RelatedLinks from '@/components/RelatedLinks.vue'
export default {
    name: 'omnibar',
    mixins: [pageGuard],
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
        ...mapActions({
            setItem: 'omnibar/setItem'
        })
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
