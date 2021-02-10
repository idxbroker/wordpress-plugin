<template>
    <idx-block className="navigation v-nav v-nav--full-bar" tag="nav">
        <slot></slot>
    </idx-block>
</template>

<script>
export default {
    name: 'Navigation'
}
</script>

<style scoped lang="scss">
.navigation {
    background-color: $gray-800;
    grid-area: sidebar;
}
</style>
