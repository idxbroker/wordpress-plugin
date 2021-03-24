<template>
    <div>
        <idx-block className="import-header__description">{{ description }}</idx-block>
        <idx-block className="import-header__actions-bar">
            <idx-block
                className="import-header__select-all import-header__select-all--loading"
                v-if="loading"
            >
                {{ selected ? 'Deselect All' : 'Select All' }}
            </idx-block>
            <idx-block
                className="import-header__select-all"
                @click="$emit('select-all', selected)"
                v-if="!loading"
            >
                {{ selected ? 'Deselect All' : 'Select All' }}
            </idx-block>
            <idx-button
                customClass="import-header__action"
                :disabled="disabled"
                @click="$emit('bulk-action')"
            >
                {{ action }} Selected
            </idx-button>
        </idx-block>
    </div>
</template>
<script>
export default {
    name: 'bulk-action',
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
        },
        disabled: {
            type: Boolean,
            default: true
        },
        loading: {
            type: Boolean,
            default: false
        }
    }
}
</script>
<style lang="scss">
.import-header__select-all--loading {
    color: $gray-400;
    cursor: default;
}
</style>
