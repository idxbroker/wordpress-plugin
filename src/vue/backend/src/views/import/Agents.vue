<template>
    <div>
        <h2>Import Agents</h2>
        <Tabbed v-bind="$props" />
    </div>
</template>
<script>
import TabbedMixin from '@/mixins/Tabbed'
import Tabbed from '@/templates/layout/Tabbed'
export default {
    mixins: [TabbedMixin],
    components: { Tabbed }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/tabContainer';
</style>
