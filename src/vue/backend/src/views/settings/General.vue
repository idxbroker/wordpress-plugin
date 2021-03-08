<template>
    <TwoColumn title="General Settings">
        <APIKey
            :apiKey="localStateValues.apiKey"
            :error="error"
            :loading="loading"
            :success="success"
            @form-field-update="formUpdate"
        />
        <GeneralSettings
            v-bind="localStateValues"
            @form-field-update="formUpdate"
        />
        <idx-button size="lg" customClass="settings-button__save " @click="saveAction">Save</idx-button>
        <template #related>
            <RelatedLinks :relatedLinks="relatedLinks"/>
        </template>
    </TwoColumn>
</template>
<script>
import { mapActions, mapState } from 'vuex'
import APIKey from '@/components/APIKey.vue'
import GeneralSettings from '@/templates/GeneralSettings'
import RelatedLinks from '@/components/RelatedLinks.vue'
import TwoColumn from '@/templates/layout/TwoColumn'
import pageGuard from '@/mixins/pageGuard'
export default {
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
            loading: false,
            success: false
        }
    },
    computed: {
        ...mapState({
            apiKey: state => state.general.apiKey,
            reCAPTCHA: state => state.general.reCAPTCHA,
            updateFrequency: state => state.general.updateFrequency,
            wrapperName: state => state.general.wrapperName
        })
    },
    methods: {
        ...mapActions({
            verifyAPIkey: 'general/verifyAPIkey',
            setItem: 'general/setItem'
        }),
        async goSave () {
            this.loading = true
            await this.verifyAPIkey()
            this.loading = false
            this.success = true
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
    }
}
</script>
<style lang="scss">
.form-content__api-key {
    margin-bottom: 40px;
}
</style>
