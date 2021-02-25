<template>
    <idx-block className="app">
        <router-view></router-view>
    </idx-block>
</template>
<script>
export default {
    name: 'app'
}
</script>
<style lang="scss">
.app {
    h2 {
        display: block;
        float: unset;
    }
}
</style>
