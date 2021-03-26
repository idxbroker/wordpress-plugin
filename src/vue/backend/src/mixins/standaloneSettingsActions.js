export default {
    data () {
        return {
            formDisabled: false
        }
    },
    methods: {
        async loadData (repo, path = '') {
            this.formDisabled = true
            try {
                const { data } = await repo.get(path)
                this.updateState(data)
            } catch (error) {
                this.errorAction()
            }
            this.formDisabled = false
        },
        updateState (data) {
            for (const key in data) {
                this.$store.dispatch(`${this.module}/setItem`, { key, value: data[key] })
            }
        },
        scrollToTop () {
            window.scrollTo({
                top: 0,
                left: 0,
                behavior: 'smooth'
            })
        },
        saveAction (saveText = '') {
            saveText = saveText === '' ? 'Changes Saved' : saveText
            this.updateState(this.formChanges)
            this.formChanges = {}
            this.scrollToTop()
            this.$store.dispatch('alerts/setItem', { key: 'notification', value: { show: true, error: false, text: saveText } })
            setTimeout(() => {
                this.$store.dispatch('alerts/setItem', { key: 'notification', value: { show: false, error: false, text: saveText } })
            }, 3000)
        },
        errorAction () {
            const errorText = 'We\'re experiencing a problem, please try again'
            this.scrollToTop()
            this.$store.dispatch('alerts/setItem', { key: 'notification', value: { show: true, error: true, text: errorText } })
            setTimeout(() => {
                this.$store.dispatch('alerts/setItem', { key: 'notification', value: { show: false, error: true, text: errorText } })
            }, 3000)
        },
        async saveHandler (repo, path = '', changes = this.formChanges, saveText = '') {
            this.formDisabled = true
            try {
                const { status } = await repo.post(changes, path)
                if (status === 204) {
                    this.saveAction(saveText)
                } else {
                    this.errorAction()
                }
                this.formDisabled = false
            } catch (error) {
                this.formDisabled = false
                if (error.response.status === 401) {
                    this.errorAction()
                    return false
                } else {
                    this.errorAction()
                }
            }
            return true
        },
        async enablePluginAction (repo) {
            this.formDisabled = true
            try {
                this.formUpdate({ key: 'enabled', value: !this.enabled })
                const { status } = await repo.post({ enabled: !this.enabled }, 'enable')
                if (status === 204) {
                    location.reload()
                } else {
                    this.errorAction()
                }
            } catch (error) {
                this.errorAction()
            }
            this.formDisabled = false
        }
    }
}
