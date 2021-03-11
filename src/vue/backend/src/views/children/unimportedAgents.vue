<template>
    <import-page-template
        cardType="agents"
        :masterList="agents"
        :description="description"
        @bulk-action="importAgents"
    ></import-page-template>
</template>
<script>
import { mapState } from 'vuex'
import importPageTemplate from '@/templates/importPageTemplate'
export default {
    name: 'unimported-agents',
    components: {
        importPageTemplate
    },
    computed: {
        ...mapState({
            agents: state => state.importContent.agents.unimported
        })
    },
    methods: {
        importAgents (e) {
            // to do: POST request agent IDs to be imported
        }
    },
    created () {
        this.description = 'Select the agents to import from IDX Broker'
    }
}
</script>
