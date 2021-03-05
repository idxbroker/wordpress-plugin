export const guidedSetupMixin = {
    props: {
        continuePath: {
            type: String,
            default: ''
        },
        skipPath: {
            type: String,
            default: ''
        }
    },
    methods: {
        goBackStep: function () {
            this.$router.go(-1)
        },
        goSkipStep: function () {
            this.$router.push({ path: this.skipPath })
        },
        async goContinue () {
            await this.saveGeneralListingsSettings()
            this.$router.push({ path: this.continuePath })
        }
    }
}
