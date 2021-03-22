<template>
    <import-page-template
        cardType="agents"
        :masterList="agents"
        :description="description"
        :clearSelections="clearSelections"
        @bulk-action="importItems($event, 'agents')"
    ></import-page-template>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import importActions from '@/mixins/importActions'
import importPageTemplate from '@/templates/importPageTemplate'
export default {
    name: 'unimported-agents',
    inject: [PRODUCT_REFS.importContent.repo],
    mixins: [importActions],
    components: {
        importPageTemplate
    },
    computed: {
        ...mapState({
            agents: state => state.importContent.agents.unimported
        })
    },
    created () {
        this.description = 'Select the agents to import from IDX Broker'
    }
}
</script>
