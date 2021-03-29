<template>
    <idx-block className="app">
        <router-view></router-view>
    </idx-block>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
/* List of repositories to inject into this component. */
const inject = Object.keys(PRODUCT_REFS).map(product => PRODUCT_REFS[product].repo)
/* Strip out social pro since we'll only check if we have an API key. */
const requiredProducts = Object.keys(PRODUCT_REFS).map(product => PRODUCT_REFS[product])
export default {
    name: 'app',
    inject,
    computed: {
        ...mapState({
            apiKey: state => state.general[PRODUCT_REFS.general.term]
        })
    },
    methods: {
        checkSocialPro () {
            this[PRODUCT_REFS.socialPro.repo].get('enable')
                .then((results) => this.handleResults(results, PRODUCT_REFS.socialPro))
                .catch((error) => {
                    console.log('error %o', error)
                })
        },
        handleResults (results, product) {
            const { data, status } = results
            if (status !== 200) {
                return false
            }
            for (const key in data) {
                this.$store.dispatch(`${product.module}/setItem`, {
                    key,
                    value: data[key]
                })
            }
        }
    },
    created () {
        const [general, listings, agents, socialPro] = requiredProducts
        Promise.all([
            /* Need: general.apiKey, listings.enabled, agents.enabled */
            /* Ex: generalRepository.get(), listingsSettingsRepository.get('enabled') */
            this[general.repo].get(general.termPath),
            this[listings.repo].get(listings.termPath),
            this[agents.repo].get(agents.termPath),
            this[socialPro.repo].get(socialPro.termPath)
        ])
            .then((results) => {
                requiredProducts.forEach((product, index) => {
                    this.handleResults(results[index], product)
                })
            })
            .catch((error) => {
                /* Todo: handle error - pass to state? */
                console.log('error %o', error)
            })
    }
}
</script>
<style lang="scss">
@import url('https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;700&display=swap');
body {
    overflow-y: scroll;
}
.app {
    font-family: 'Mulish', sans-serif;
    h2 {
        display: block;
        float: unset;
    }
}
</style>
