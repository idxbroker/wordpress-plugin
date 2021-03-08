<template>
    <div>
        <bulk-action
            action="Delete"
            :selected="selected"
            :description="description"
            :disabled="agentsSelected.length === 0"
            @select-all="selectAll('agent-checkbox', agents)"
            @bulk-action="unimportAgents"
        ></bulk-action>
        <idx-block className="agents-card__group">
            <agent-card
                v-for="agent in agents"
                ref="agent-checkbox"
                :key="agent.agentID"
                :agent="agent"
                :imported="true"
                @agent-selected="updateSelected($event, agentsSelected)"
                @remove-agent="unimportAgents"
            >
            </agent-card>
        </idx-block>
    </div>
</template>
<script>
import { mapState } from 'vuex'
import importPages from '@/mixins/importPages'
import BulkAction from '../../components/BulkAction.vue'
import agentCard from '../../components/agentCard.vue'
export default {
    name: 'imported-agents',
    mixins: [importPages],
    components: {
        BulkAction,
        agentCard
    },
    data () {
        return {
            agentsSelected: []
        }
    },
    computed: {
        ...mapState({
            agents: state => state.importContent.agents.imported
        })
    },
    methods: {
        unimportAgents (agentId) {
            // to do: DELETE request agent IDs to be removed
            if (agentId) {
                // remove a singular id
            } else {
                // remove the selected agents list
            }
        }
    },
    created () {
        this.description = 'Select the imported agents to be deleted from IMPress'
    }
}
</script>
