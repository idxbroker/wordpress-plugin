export default {
    data () {
        return {
            checkProgress: '',
            clearSelections: false
        }
    },
    methods: {
        async importItems (items, key) {
            this.clearSelections = false
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
                        }
                    }, 5000)
                } else {
                    this.clearSelections = true
                    this.$store.dispatch('importContent/setItem', { key, value: data })
                }
            } catch (error) {
                // To-do: error banner
            }
        },
        async unimportItems (items, key) {
            this.clearSelections = false
            const itemIds = items.map(x => { return x.postId })
            const { data } = await this.importContentRepository.delete({ ids: itemIds }, `${key}/delete`)
            this.$store.dispatch(`${this.module}/setItem`, { key, value: data })
            this.clearSelections = true
        }
    },
    created () {
        this.module = 'importContent'
    }
}
