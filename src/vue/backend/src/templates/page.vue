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
body {
    overflow-y: scroll;
}
.content-page {
  --font-size-h1: 31px;
  --font-size-h2: 25px;
  --font-size-p: 16px;
  --font-size-p-large: 18px;
  --header-height: 83px;
  --line-height-h1: 28px;
  --line-height-h2: 28px;
  --line-height-p: 28px;
  --line-height-p-large: 28px;
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

    h1 {
        font-size: var(--font-size-h1);
        font-weight: 300;
        line-height: var(--line-height-h1);
    }

    h2 {
        font-size: var(--font-size-h2);
        line-height: var(--line-height-h2);
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
