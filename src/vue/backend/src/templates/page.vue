<template>
    <idx-block :className="{
            'content-page': true,
            'content-page--slide-left': !expanded
        }"
    >
        <slot></slot>
    </idx-block>
</template>
<script>
import { mapState } from 'vuex'
export default {
    name: 'page',
    computed: {
        ...mapState({
            expanded: state => state.routes.expanded
        })
    }
}
</script>
<style lang="scss">
.content-page {
    padding: 50px;
    margin: 83px 50px 0px 255px;
    background-color: $white;
    transition: margin-left .5s ease;
    &--slide-left {
        margin-left: 50px;
    }
    p.lead {
        font-weight: 400;
    }
}
</style>
