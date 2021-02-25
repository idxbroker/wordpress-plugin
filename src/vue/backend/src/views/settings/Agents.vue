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
export default {
    components: {
        AgentsSettings,
        TwoColumn
    }
}
</script>
