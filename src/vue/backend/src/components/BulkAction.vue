<template>
    <div>
        <idx-block className="import-header__description">{{ description }}</idx-block>
        <idx-block className="import-header__actions-bar">
            <idx-block className="import-header__select-all" @click="$emit('select-all', selected)">{{ selected ? 'Select All' : 'Deselect All' }}</idx-block>
            <idx-button customClass="import-header__action" @click="$emit('bulk-action', action)">{{ action }} Selected</idx-button>
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
        }
    }
}
</script>
