<template>
    <TwoColumn title="General Settings">
        <idx-block className="form-content__label">
            <idx-block tag="h2" className="form-content__title">Connect Your IDX Broker Account</idx-block>
            <p>Description of API key and why it’s needed.</p>
        </idx-block>
        <APIKey
            :apiKey="localStateValues.apiKey"
            :disabled="formDisabled"
            :error="error"
            :loading="formDisabled"
            :success="success"
            @form-field-update="formUpdate"
        />
        <GeneralSettings
            :formDisabled="formDisabled"
            v-bind="localStateValues"
            @form-field-update="formUpdate"
        />
        <idx-button size="lg" @click="saveHandler">Save</idx-button>
        <template #related>
            <RelatedLinks :relatedLinks="relatedLinks"/>
        </template>
    </TwoColumn>
</template>
<script>
import { PRODUCT_REFS } from '@/data/productTerms'
import APIKey from '@/components/APIKey.vue'
import GeneralSettings from '@/templates/GeneralSettings'
import RelatedLinks from '@/components/RelatedLinks.vue'
import TwoColumn from '@/templates/layout/TwoColumn'
import pageGuard from '@/mixins/pageGuard'
export default {
    name: 'standalone-general-settings',
    inject: [PRODUCT_REFS.general.repo],
    mixins: [pageGuard],
    components: {
        APIKey,
        GeneralSettings,
        RelatedLinks,
        TwoColumn
    },
    data () {
        return {
            error: false,
            success: false,
            formDisabled: false
        }
    },
    methods: {
        async saveHandler () {
            // To Do: user facing error checking
            if (this.formIsUpdated) {
                this.formDisabled = true
                try {
                    await this.generalRepository.post(this.formChanges)
                    this.formDisabled = false
                    this.saveAction()
                    this.success = true
                    this.error = false
                } catch (error) {
                    this.formDisabled = false
                    if (error.response.status === 401) {
                        this.error = true
                        this.success = false
                    } else {
                        // full form error response
                        this.errorAction()
                    }
                }
            } else {
                this.continue()
            }
        }
    },
    async created () {
        this.module = 'general'
        this.relatedLinks = [
            {
                text: 'Where can I find my API key?',
                href: '#where'
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
        this.errorMessage = 'We couldn’t find an account with the provided API key'
        const { data } = await this.generalRepository.get()
        for (const key in data) {
            this.$store.dispatch(`${this.module}/setItem`, { key, value: data[key] })
        }
    }
}
</script>
