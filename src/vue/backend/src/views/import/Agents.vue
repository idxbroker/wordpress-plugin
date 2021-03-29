<template>
    <idx-block className="section">
        <template v-if="!enabled || !isValid">
            <feedback
                :title="title"
                :link="link"
                :content="content"
                :missingAPI="!isValid"
            ></feedback>
        </template>
        <template v-else>
            <h2>Import Agents</h2>
            <Tabbed v-bind="$props" />
        </template>
    </idx-block>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import TabbedMixin from '@/mixins/Tabbed'
import Tabbed from '@/templates/layout/Tabbed'
import Feedback from '@/components/importFeedback.vue'
export default {
    name: 'import-idx-agents-container',
    mixins: [TabbedMixin],
    inject: [PRODUCT_REFS.importContent.repo],
    components: {
        Tabbed,
        Feedback
    },
    watch: {
        enabled () {
            this.loadAgents()
        }
    },
    computed: {
        ...mapState({
            enabled: state => state.agentSettings.enabled,
            isValid: state => state.general.isValid
        }),
        title () {
            return this.isValid ? 'Impress Agents is Disabled' : 'API Key Required'
        },
        link () {
            return '/settings/agents'
        },
        content () {
            return {
                startingStatement: 'To import agents, you need to',
                warningLink: 'enable IMPress Agents',
                closingStatement: 'and ensure that your API key is active'
            }
        }
    },
    methods: {
        async loadAgents () {
            if (this.enabled && this.isValid) {
                this.$store.dispatch(`${this.module}/setItem`, { key: 'mainLoading', value: true })
                const { data } = await this.importContentRepository.get('agents')
                this.$store.dispatch(`${this.module}/setItem`, { key: 'agents', value: data })
                this.$store.dispatch(`${this.module}/setItem`, { key: 'mainLoading', value: false })
            }
        }
    },
    mounted () {
        this.loadAgents()
    },
    created () {
        this.module = 'importContent'
        this.description = 'Select the agents to import from IDX Broker'
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/tabContainer';
</style>
