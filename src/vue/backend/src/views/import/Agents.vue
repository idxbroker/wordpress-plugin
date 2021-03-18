<template>
    <div>
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
    </div>
</template>
<script>
import { mapState } from 'vuex'
import TabbedMixin from '@/mixins/Tabbed'
import Tabbed from '@/templates/layout/Tabbed'
import Feedback from '@/components/importFeedback.vue'
export default {
    mixins: [TabbedMixin],
    components: {
        Tabbed,
        Feedback
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
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/tabContainer';
</style>
