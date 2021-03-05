export default {
    methods: {
        goBackStep: function () {
            this.$router.go(-1)
        },
        goSkipStep: function () {
            this.$router.push({ path: this.skipPath })
        },
        goContinue: function () {
            this.$router.push({ path: this.continuePath })
        }
    },
    created () {
        this.continuePath = ''
        this.skipPath = ''
    }
}
