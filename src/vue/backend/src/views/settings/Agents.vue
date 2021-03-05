<template>
    <TwoColumn title="IMPress Agents Settings">
        <AgentsSettings />
        <template #related>
            Related Links here
        </template>
    </TwoColumn>
</template>
<script>
import AgentsSettings from '@/templates/AgentsSettings'
import TwoColumn from '@/templates/layout/TwoColumn'
import pageGuard from '@/mixins/pageGuard'
export default {
    mixins: [pageGuard],
    components: {
        AgentsSettings,
        TwoColumn
    },
    created () {
        this.module = 'agentSettings'
    }
}
</script>
