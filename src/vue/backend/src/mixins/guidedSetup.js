import { mapState } from 'vuex'
export default {
    computed: {
        ...mapState({
            hasChanges: state => state.guidedSetup.hasChanges
        })
    },
    methods: {
        goBackStep () {
            this.$router.go(-1)
        },
        goSkipStep () {
            this.$router.push({ path: this.skipPath })
        },
        updateState (data) {
            for (const key in data) {
                this.$store.dispatch(`${this.module}/setItem`, { key, value: data[key] })
            }
        },
        saveHandler (moduleKey, moduleName = '', path = '') {
            if (this.formIsUpdated) {
                this.$store.dispatch('guidedSetup/setItem', { key: 'hasChanges', value: true })
                this.$store.dispatch('guidedSetup/setItem', {
                    key: moduleKey,
                    value: {
                        changes: moduleKey === 'omnibar' ? this.localStateValues : this.formChanges,
                        module: moduleName !== '' ? moduleName : this.module,
                        path
                    }
                })
                this.formChanges = {}
                this.$router.push({ path: this.continuePath })
            } else {
                this.$router.push({ path: this.continuePath })
            }
        }
    },
    beforeRouteLeave (to, from, next) {
        if (to.path === '/settings/general' && this.hasChanges) {
            const answer = window.confirm('Do you really want to leave? You have unsaved changes!')
            if (answer) {
                next()
            } else {
                next(false)
            }
        } else {
            next()
        }
    }
}
