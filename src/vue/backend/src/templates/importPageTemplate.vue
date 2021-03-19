<template>
    <div>
        <bulk-action
            :action="action"
            :selected="selected"
            :description="description"
            :disabled="itemsSelected.length === 0"
            @select-all="selectAll( masterList)"
            @bulk-action="$emit('bulk-action', itemsSelected)"
        ></bulk-action>
        <idx-block v-if="masterList.length" :className="['import-card__group', `import-card__group-${cardType}`]">
            <template v-if="cardType === 'listings'">
                <listings-card
                    v-for="listing in masterList"
                    ref="card-checkbox"
                    :key="listing.listingID"
                    :listing="listing"
                    @listing-selected="updateSelected($event)"
                ></listings-card>
            </template>
            <template v-else>
                <agent-card
                    v-for="agent in masterList"
                    ref="card-checkbox"
                    :key="agent.agentID"
                    :agent="agent"
                    :imported="imported"
                    @agent-selected="updateSelected($event)"
                    @remove-agent="$emit('remove-agent', $event)"
                >
                </agent-card>
            </template>
        </idx-block>
        <idx-block v-else>
            There are no {{ cardType }} available.
        </idx-block>
    </div>
</template>
<script>
import BulkAction from '@/components/BulkAction.vue'
import importPages from '@/mixins/importPages'
import listingsCard from '@/components/listingsCard'
import agentCard from '@/components/agentCard'
export default {
    name: 'imported-templated',
    mixins: [importPages],
    components: {
        BulkAction,
        listingsCard,
        agentCard
    },
    props: {
        description: {
            type: String,
            default: ''
        },
        masterList: {
            type: Array,
            required: true
        },
        action: {
            type: String,
            default: 'Import'
        },
        cardType: {
            type: String,
            required: true
        },
        imported: {
            type: Boolean,
            default: false
        },
        clearSelections: {
            type: Boolean,
            default: false
        }
    }
}
</script>
<style lang="scss">
.import-card__group {
    display: flex;
    flex-wrap: wrap;
    grid-gap: 15px;
    margin-top: 30px;
}
</style>
