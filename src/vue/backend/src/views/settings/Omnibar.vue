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
import pageGuard from '@/mixins/pageGuard'
export default {
    name: 'omnibar',
    mixins: [pageGuard],
    components: {
        TwoColumn
    },
    created () {
        this.module = 'omnibar'
    }
}
</script>
