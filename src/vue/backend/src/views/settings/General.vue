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
            :showRefresh="true"
            @refreshPluginOptions="refreshPluginOptions"
            @form-field-update="formUpdate"
        />
        <GeneralSettings
            :formDisabled="formDisabled"
            v-bind="localStateValues"
            @form-field-update="formUpdate"
        />
        <idx-button size="lg" @click="save">Save</idx-button>
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
import standaloneSettingsActions from '@/mixins/standaloneSettingsActions'
const { general: { repo } } = PRODUCT_REFS
export default {
    name: 'standalone-general-settings',
    inject: [repo],
    mixins: [pageGuard, standaloneSettingsActions],
    components: {
        APIKey,
        GeneralSettings,
        RelatedLinks,
        TwoColumn
    },
    data () {
        return {
            error: false,
            success: false
        }
    },
    methods: {
        save () {
            this.saveHandler(this[repo])
        },
        refreshPluginOptions () {
            this.saveHandler(this[repo], '', this.localStateValues.APIKey, 'Plugin Options have been refreshed.')
        }
    },
    created () {
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
        this.loadData(this[repo])
    }
}
</script>
