<template>
    <TwoColumn title="IMPress Listings Settings">
        <Tabbed v-bind="$props" />
        <template #related>
            Related Links here
        </template>
    </TwoColumn>
</template>
<script>
import TwoColumn from '@/templates/layout/TwoColumn'
import TabbedMixin from '@/mixins/Tabbed'
import Tabbed from '@/templates/layout/Tabbed'
export default {
    mixins: [TabbedMixin],
    components: {
        Tabbed,
        TwoColumn
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/tabContainer';
</style>
