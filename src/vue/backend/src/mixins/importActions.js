export default {
    data () {
        return {
            checkProgress: '',
            clearSelections: false,
            loading: false
        }
    },
    methods: {
        scrollToTop () {
            window.scrollTo({
                top: 0,
                left: 0,
                behavior: 'smooth'
            })
        },
        async importItems (items, key) {
            this.clearSelections = false
            this.loading = true
            this.scrollToTop()
            this.$store.dispatch('alerts/setItem', { key: 'notification', value: { show: true, error: false, text: 'Your Import Is In Progress' } })
            const itemIds = items.map(x => {
                return key === 'listings' ? x.listingId : x.agentId
            })
            try {
                await this.importContentRepository.post({ ids: itemIds }, `${key}/import`)
                let { data } = await this.importContentRepository.get(key)
                if (data.inProgress) {
                    this.checkProgress = setInterval(async () => {
                        if (data.inProgress) {
                            const check = await this.importContentRepository.get(key)
                            data = check.data
                            this.clearSelections = true
                        } else {
                            clearInterval(this.checkProgress)
                            this.$store.dispatch('importContent/setItem', { key, value: data })
                            this.loading = false
                            this.$store.dispatch('alerts/setItem', { key: 'notification', value: { show: false, error: false, text: 'Your Import Is In Progress' } })
                        }
                    }, 5000)
                } else {
                    this.clearSelections = true
                    this.$store.dispatch('importContent/setItem', { key, value: data })
                    this.loading = false
                    this.$store.dispatch('alerts/setItem', { key: 'notification', value: { show: false, error: false, text: 'Your Import Is In Progress' } })
                }
            } catch (error) {
                this.$store.dispatch('alerts/setItem', { key: 'notification', value: { show: true, error: true, text: 'We\'re experiencing a problem, please try again' } })
            }
        },
        async unimportItems (items, key) {
            this.clearSelections = false
            this.loading = true
            this.$store.dispatch('alerts/setItem', { key: 'notification', value: { show: true, error: false, text: 'Your Deletion Is In Progress' } })
            const itemIds = items.map(x => { return x.postId })
            try {
                this.$store.dispatch('alerts/setItem', { key: 'notification', value: { show: false, error: false, text: 'Your Deletion Is In Progress' } })
                const { data } = await this.importContentRepository.delete({ ids: itemIds }, `${key}/delete`)
                this.$store.dispatch(`${this.module}/setItem`, { key, value: data })
                this.clearSelections = true
                this.loading = false
            } catch (error) {
                this.$store.dispatch('alerts/setItem', { key: 'notification', value: { show: true, error: true, text: 'We\'re experiencing a problem, please try again' } })
            }
        }
    },
    created () {
        this.module = 'importContent'
    }
}
