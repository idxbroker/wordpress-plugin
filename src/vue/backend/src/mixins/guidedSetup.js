export default {
    methods: {
        goBackStep: function () {
            this.$router.go(-1)
        },
        goSkipStep: function () {
            this.$router.push({ path: this.skipPath })
        }
    },
    created () {
        this.skipPath = ''
    }
}
