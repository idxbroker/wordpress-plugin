<template>
    <idx-block
        :className="{
            'import-page-template': true,
            'import-page-template--loading': loading
        }">
        <bulk-action
            :action="action"
            :selected="selected"
            :description="description"
            :disabled="itemsSelected.length === 0"
            :loading="loading"
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
            <idx-block className="spinner-border" role="status" v-if="loading">
                <idx-block tag="span" className="visually-hidden">Loading...</idx-block>
            </idx-block>
        </idx-block>
        <idx-block v-else>
            <idx-block className="spinner-border" role="status" v-if="itemsSelected.length > 0">
                <idx-block tag="span" className="visually-hidden">Loading...</idx-block>
            </idx-block>
            <idx-block v-else>
                There are no {{ cardType }} available.
            </idx-block>
        </idx-block>
    </idx-block>
</template>
<script>
import BulkAction from '@/components/BulkAction.vue'
import importTemplate from '@/mixins/importTemplate'
import listingsCard from '@/components/listingsCard'
import agentCard from '@/components/agentCard'
export default {
    name: 'imported-templated',
    mixins: [importTemplate],
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
        },
        loading: {
            type: Boolean,
            default: false
        }
    }
}
</script>
<style lang="scss">
.import-page-template {

    .import-card__group {
        display: flex;
        flex-wrap: wrap;
        grid-gap: 15px;
        margin-top: 30px;
        position: relative;

        .spinner-border {
            bottom: 50%;
            margin-bottom: -2rem;
            margin-right: -2rem;
            position: absolute;
            right: 50%;
        }
    }

    .spinner-border {
        border-width: 4px;
        height: 4rem;
        width: 4rem;
    }

    &--loading {

        .import-card__group .import-listings,
        .import-card__group .agent-card {
            filter: grayscale(1);
            opacity: .2;
        }
    }
}
</style>
