export default {
    data () {
        return {
            selected: false
        }
    },
    methods: {
        updateSelected (e, actionsArray) {
            if (e[0]) {
                actionsArray.push(e[1])
            } else {
                actionsArray.splice(actionsArray.indexOf(e[1]), 1)
            }
        },
        selectAll (reference, masterList) {
            const checkboxList = this.$refs[reference]
            for (let i = 0; i < checkboxList.length; i++) {
                checkboxList[i].$children[0].changeActions(masterList[i], !this.selected)
            }
            this.selected = !this.selected
        }
    }
}
