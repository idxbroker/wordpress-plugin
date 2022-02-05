export default {
    props: {
        parentRoute: {
            type: String,
            required: true
        },
        tabbedRoutes: {
            type: Array,
            default: () => []
        }
    },
    computed: {
        tabs () {
            return this.tabbedRoutes.map(route => route.label)
        },
        activeTab () {
            const { fullPath } = this.$route
            let index = 0
            this.tabbedRoutes.forEach((route, i) => {
                if (`${this.parentRoute}/${route.path}` === fullPath) {
                    index = i
                }
            })
            return index
        }
    },
    methods: {
        switchTab (index) {
            /* Prevent redundant navigation error */
            if (index !== this.activeTab) {
                const route = this.tabbedRoutes[index].path
                this.$router.push(`${this.parentRoute}/${route}`)
            }
        }
    }
}
