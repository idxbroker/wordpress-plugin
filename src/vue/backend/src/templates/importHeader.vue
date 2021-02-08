<template>
    <idx-tab-container
        customClass="import-header"
        @activeTab="switchTabs"
        :activeTab="activeTab"
        :tabs="['UNIMPORTED', 'IMPORTED']"
    >
        <idx-block className="import-header__description">{{ description }}</idx-block>
        <idx-block className="import-header__actions-bar">
            <idx-block className="import-header__select-all" @click="$emit('selectAll', selected)">{{ selected ? 'Select All' : 'Deselect All' }}</idx-block>
            <idx-button customClass="import-header__action" @click="$emit('bulkAction', action)">{{ action }} Selected</idx-button>
        </idx-block>
        <router-view></router-view>
    </idx-tab-container>
</template>
<script>
export default {
    name: 'import-header',
    data () {
        return {
            activeTab: 0
        }
    },
    props: {
        selected: {
            type: Boolean,
            default: false
        },
        action: {
            type: String,
            default: 'Import'
        },
        description: {
            type: String,
            default: ''
        }
    },
    methods: {
        switchTabs (e) {
            this.activeTab = e
            /* Router change here, once we have router set up we can switch it. For now emitting and event */
            this.$emit('switchTabs', this.activeTab)
        }
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/tabContainer';
.import-header {
    background-color: $white;
    &__description {
        padding: 20px 25px;
        background-color: $gray-150;
        color: $gray-800;
        font-size: 16px;
    }
    &__actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 25px;
    }
    &__select-all {
        font-size: 16px;
        color: $cyan;
        cursor: pointer;
    }
    &__action {
        padding: 8px 12px;
        font-weight: 600;
        font-size: 16px;
        letter-spacing: 0.4px;
    }
    .tab-container {
        &__tabs {
            justify-content: end;
            border-bottom: 1px solid $gray-150;
            height: 50px;
        }
        &__tab {
            width: 164px;
            margin-left: 0px;
            flex: unset;
            font-size: 16px;
            letter-spacing: 1.6px;
            color: #788088;
            &:first-child {
                margin-right: 18px;
            }
        }
    }
}

</style>
