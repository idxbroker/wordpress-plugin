<template>
    <import-page-template
        cardType="agents"
        action="delete"
        :masterList="agents"
        :description="description"
        :imported="true"
        :clearSelections="clearSelections"
        @bulk-action="unimportItems($event, 'agents')"
        @remove-agent="unimportItems($event, 'agents')"
    ></import-page-template>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import importActions from '@/mixins/importActions'
import importPageTemplate from '@/templates/importPageTemplate'
export default {
    name: 'imported-agents',
    mixins: [importActions],
    inject: [PRODUCT_REFS.importContent.repo],
    components: {
        importPageTemplate
    },
    computed: {
        ...mapState({
            agents: state => state.importContent.agents.imported
        })
    },
    created () {
        this.description = 'Select the imported agents to be deleted from IMPress'
    }
}
</script>
