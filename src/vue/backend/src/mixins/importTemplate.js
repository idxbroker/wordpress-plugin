export default {
    data () {
        return {
            selected: false,
            itemsSelected: []
        }
    },
    watch: {
        clearSelections (newVal, oldVal) {
            if (newVal) {
                const checkboxList = this.$refs['card-checkbox']
                for (let i = 0; i < checkboxList.length; i++) {
                    checkboxList[i].$children[0].changeActions(this.masterList[i], false)
                }
                this.selected = false
            }
        }
    },
    methods: {
        updateSelected (e) {
            const inArray = this.itemsSelected.indexOf(e[1])
            if (e[0]) {
                if (inArray === -1) {
                    this.itemsSelected.push(e[1])
                }
            } else {
                this.itemsSelected.splice(inArray, 1)
            }
        },
        selectAll (masterList) {
            const checkboxList = this.$refs['card-checkbox']
            for (let i = 0; i < checkboxList.length; i++) {
                checkboxList[i].$children[0].changeActions(masterList[i], !this.selected)
            }
            this.selected = !this.selected
        }
    }
}
