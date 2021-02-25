<template>
    <TwoColumn title="Omnibar Settings">
        <div>Omnibar Settings Here</div>
        <template #related>
            Related Links here
        </template>
    </TwoColumn>
</template>
<script>
import TwoColumn from '@/templates/layout/TwoColumn'
export default {
    name: 'omnibar',
    components: {
        TwoColumn
    }
}
</script>
