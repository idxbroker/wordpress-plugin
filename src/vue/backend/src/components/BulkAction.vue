<template>
    <div>
        <idx-block className="import-header__description">{{ description }}</idx-block>
        <idx-block className="import-header__actions-bar">
            <idx-block
                :className="{
                    'import-header__select-all': true,
                    'import-header__select-all--loading': loading
                }"
                @click="loading ? false : $emit('select-all', selected)"
            >
                {{ selected ? 'Deselect All' : 'Select All' }}
            </idx-block>
            <idx-button
                customClass="import-header__action"
                :disabled="disabled || loading"
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
