<template>
    <idx-block :className="{
            'content-page': true,
            'content-page--slide-left': !expanded
        }"
    >
        <SvgSprite/>
        <slot></slot>
    </idx-block>
</template>
<script>
import { mapState } from 'vuex'
import SvgSprite from '@/components/SvgSprite.vue'
export default {
    name: 'page',
    components: {
        SvgSprite
    },
    computed: {
        ...mapState({
            expanded: state => state.routes.expanded
        })
    }
}
</script>

<style lang="scss">
@import '@/styles/formContentStyles.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons';
body {
    overflow-y: scroll;
}
.content-page {
    padding: 50px;
    margin: 135px 30px 0 300px;
    background-color: $white;
    font-size: var(--font-size-p);
    line-height: var(--line-height-p);
    position: relative;
    transition: margin-left .5s ease;
    overflow: auto;

    h1,h2,h3,h4,h5,h6 {
        // reset styles
        color: inherit;
        display: block;
        float: unset;
    }

    p {
        font-size: inherit;
        line-height: inherit;

        &.lead {
            font-weight: 400;
        }
    }

    &--slide-left {
        margin-left: 90px;
    }

    @media (max-width: 782px) {
        transition: none;
        margin-left: 50px;
    }
}
</style>
