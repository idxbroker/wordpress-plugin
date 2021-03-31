<template>
    <div></div>
</template>
<script>
import { mapState } from 'vuex'
export default {
    name: 'redirect-page',
    computed: {
        ...mapState({
            apiKey: state => state.general.apiKey
        })
    },
    mounted () {
        if (this.apiKey) {
            this.$router.push({ path: '/settings/general' })
        } else {
            this.$router.push({ path: '/guided-setup/welcome' })
        }
    }
}
</script>
