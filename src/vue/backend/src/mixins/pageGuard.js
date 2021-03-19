export default {
    data () {
        return {
            formChanges: {},
            module: ''
        }
    },
    computed: {
        localStateValues () {
            return { ...this.$store.state[this.module], ...this.formChanges, ...this.$store.state.guidedSetup[this.module].changes }
        },
        formIsUpdated () {
            return Object.keys(this.formChanges).length > 0
        }
    },
    methods: {
        formUpdate (e) {
            const change = {
                [e.key]: e.value
            }
            this.formChanges = { ...this.formChanges, ...change }
        },
        updateState (changes) {
            for (const key in changes) {
                this.$store.dispatch(`${this.module}/setItem`, { key, value: changes[key] })
            }
        },
        saveAction () {
            this.updateState(this.formChanges)
            this.formChanges = {}
        }
    },
    beforeRouteLeave (to, from, next) {
        if (!this.formIsUpdated) {
            next()
        } else {
            const answer = window.confirm('Do you really want to leave? You have unsaved changes!')
            if (answer) {
                next()
            } else {
                next(false)
            }
        }
    }
}
