<template>
    <import-page-template
        cardType="agents"
        action="delete"
        :masterList="agents"
        :description="description"
        :imported="true"
        @bulk-action="unimportAgents"
        @remove-agent="unimportAgents"
    ></import-page-template>
</template>
<script>
import { mapState } from 'vuex'
import importPageTemplate from '@/templates/importPageTemplate'
export default {
    name: 'imported-agents',
    components: {
        importPageTemplate
    },
    computed: {
        ...mapState({
            agents: state => state.importContent.agents.imported
        })
    },
    methods: {
        unimportAgents (agentId) {
            // to do: DELETE request agent IDs to be removed
        }
    },
    created () {
        this.description = 'Select the imported agents to be deleted from IMPress'
    }
}
</script>
