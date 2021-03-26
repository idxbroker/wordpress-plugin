<template>
    <TwoColumn title="General Settings">
        <idx-block className="form-content__label">
            <idx-block tag="h2" className="form-content__title">Connect Your IDX Broker Account</idx-block>
            <p>Your API Key is required to access data from the API. The API allows you to use the plugin to access feature properties, custom city lists, and more.</p>
        </idx-block>
        <APIKey
            :apiKey="localStateValues.apiKey"
            :devPartnerKey="localStateValues.devPartnerKey"
            :disabled="formDisabled"
            :error="error || (!isValid && !loading)"
            :loading="formDisabled || loading"
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
import { mapState } from 'vuex'
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
            success: false,
            loading: false
        }
    },
    computed: {
        ...mapState({
            isValid: state => state.general.isValid
        })
    },
    methods: {
        save (path = '', changes = this.formChanges, saveText = '') {
            this.error = false
            this.success = false
            this.loading = true
            this.saveHandler(this[repo], path, changes, saveText).then(async x => {
                this.loading = true
                const { data } = await this[repo].get('apiKeyIsValid')
                this.$store.dispatch(`${this.module}/setItem`, { key: 'isValid', value: data.isValid })
                this.loading = false
                this.error = !data.isValid
                this.success = data.isValid
                this.scrollToTop()
            })
        },
        refreshPluginOptions () {
            this.save('', { apiKey: this.localStateValues.apiKey }, 'Plugin Options have been refreshed.')
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
