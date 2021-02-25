<template>
    <TwoColumn title="General Settings">
        <GeneralSettings />
        <template #related>
            Related Links here
        </template>
    </TwoColumn>
</template>
<script>
import TwoColumn from '@/templates/layout/TwoColumn'
import GeneralSettings from '@/templates/GeneralSettings'
export default {
    components: {
        GeneralSettings,
        TwoColumn
    }
}
</script>
