<template>
    <div></div>
</template>
<script>
import { PRODUCT_REFS } from '@/data/productTerms'
import { mapState } from 'vuex'
const { general: { repo } } = PRODUCT_REFS
export default {
    name: 'redirect-page',
    inject: [repo],
    computed: {
        ...mapState({
            apiKey: state => state.general.apiKey,
            loadContent: state => state.alerts.loadContent
        })
    },
    watch: {
        loadContent (newVal) {
            if (newVal) {
                this.apiKey ? this.$router.push({ path: '/settings/general' }) : this.$router.push({ path: '/guided-setup/welcome' })
            }
        }
    },
    async mounted () {
        this.module = 'general'
        try {
            const { data } = await this[repo].get()
            for (const key in data) {
                this.$store.dispatch(`${this.module}/setItem`, { key, value: data[key] })
            }
        } catch (error) {
            // if they do not have a valid api key, they should still be redirected to the settings general page
            console.error(error)
        }
        if (this.loadContent) {
            this.apiKey ? this.$router.push({ path: '/settings/general' }) : this.$router.push({ path: '/guided-setup/welcome' })
        }
    }
}
</script>
