import isEqual from 'lodash/isEqual'
export default {
    data () {
        return {
            formChanges: {},
            module: ''
        }
    },
    computed: {
        localStateValues () {
            return { ...this.$store.state[this.module], ...this.formChanges }
        },
        routeCanLeave () {
            return isEqual({ ...this.$store.state[this.module] }, this.localStateValues)
        }
    },
    methods: {
        formUpdate (e) {
            const change = {
                [e.key]: e.value
            }
            this.formChanges = { ...this.formChanges, ...change }
        },
        saveAction () {
            for (const item in this.formChanges) {
                this.setItem({ key: item, value: this.formChanges[item] })
            }
            this.formChanges = {}
        }
    },
    beforeRouteLeave (to, from, next) {
        if (this.routeCanLeave) {
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
