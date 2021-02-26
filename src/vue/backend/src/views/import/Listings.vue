<template>
    <section>
        <h2>Import IDX Listings</h2>
        <Tabbed v-bind="$props" />
    </section>
</template>
<script>
import TabbedMixin from '@/mixins/Tabbed'
import Tabbed from '@/templates/layout/Tabbed'
export default {
    mixins: [TabbedMixin],
    components: { Tabbed }
}
</script>
