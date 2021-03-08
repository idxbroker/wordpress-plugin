<template>
    <div>
        <bulk-action
            :selected="selected"
            :description="description"
            :disabled="agentsSelected.length === 0"
            @select-all="selectAll('agent-checkbox', agents)"
            @bulk-action="importAgents"
        ></bulk-action>
        <idx-block className="agents-card__group">
            <agent-card
                v-for="agent in agents"
                ref="agent-checkbox"
                :key="agent.agentID"
                :agent="agent"
                @agent-selected="updateSelected($event, agentsSelected)"
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
    name: 'unimported-agents',
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
            agents: state => state.importContent.agents.unimported
        })
    },
    methods: {
        importAgents () {
            // to do: POST request agent IDs to be imported
        }
    },
    created () {
        this.description = 'Select the agents to import from IDX Broker'
    }
}
</script>
