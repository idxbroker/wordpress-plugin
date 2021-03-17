<template>
    <idx-tab-container
        customClass="import-header"
        @activeTab="switchTab"
        :activeTab="activeTab"
        :tabs="tabs"
    >
        <router-view></router-view>
    </idx-tab-container>
</template>
<script>
import Tabbed from '@/mixins/Tabbed'
export default {
    name: 'tabbed',
    mixins: [Tabbed]
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/tabContainer';
.import-header.tab-container {
    padding: 0;
}
.import-header {
    padding: 0;
    background-color: $white;
    font-size: 1rem;
    &__description {
        padding: 20px 25px;
        background-color: $gray-150;
        color: $gray-800;
    }
    &__actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 25px;
    }
    &__select-all {
        color: $cyan;
        cursor: pointer;
    }
    &__action {
        padding: 8px 12px;
        font-weight: 700;
        letter-spacing: 0.4px;
    }
    .tab-container {
        &__header {
            border-bottom: 1px solid $gray-150;
        }
        &__tabs {
            justify-content: end;
            height: 50px;
            gap: 1rem;
            overflow: visible;
        }
        &__tab {
            width: 164px;
            margin-left: 0px;
            margin-bottom: 0px;
            flex: unset;

            letter-spacing: 1.6px;
            color: #788088;
            text-transform: uppercase;

            &--active {
                height: 51px;
            }
        }
    }
}

</style>
