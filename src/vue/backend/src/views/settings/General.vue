<template>
    <TwoColumn title="General Settings">
        <APIKey :error="error" :loading="loading" :success="success"/>
        <GeneralSettings />
        <idx-button size="lg" @click="goSave">Save</idx-button>
        <template #related>
            <RelatedLinks :relatedLinks="relatedLinks"/>
        </template>
    </TwoColumn>
</template>
<script>
import { mapActions } from 'vuex'
import APIKey from '@/components/APIKey.vue'
import GeneralSettings from '@/templates/GeneralSettings'
import RelatedLinks from '@/components/RelatedLinks.vue'
import TwoColumn from '@/templates/layout/TwoColumn'
export default {
    components: {
        APIKey,
        GeneralSettings,
        RelatedLinks,
        TwoColumn
    },
    data () {
        return {
            error: false,
            errorMessage: 'We couldn’t find an account with the provided API key',
            loading: false,
            success: false,
            relatedLinks: [
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
        }
    },
    methods: {
        ...mapActions({
            verifyAPIkey: 'general/verifyAPIkey'
        }),
        async goSave () {
            this.loading = true
            await this.verifyAPIkey()
            this.loading = false
            this.success = true
        }
    }
}
</script>
