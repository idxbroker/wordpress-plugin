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
    components: { SvgSprite },
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
    // Spacing
    --space-4: 16px;
    --space-5: 20px;
    --space-6: 24px;
    --space-8: 32px;
    --space-9: 36px;
    --space-10: 40px;
    --space-12: 48px;
    --space-15: 60px;
    --space-18: 72px;
    // Typography
    --font-size-h1: 31px;
    --font-size-h2: 25px;
    --font-size-h3: 21px;
    --font-size-p: 16px;
    --font-size-p-large: 18px;
    --letter-spacing-h3: 2.1px;
    --line-height-h1: 28px;
    --line-height-h2: 28px;
    --line-height-h3: 22px;
    --line-height-p: 28px;
    --line-height-p-large: 28px;
    --dialog-header-height: 48px;
    background-color: $white;
    font-size: var(--font-size-p);
    height: 100vh;
    line-height: var(--line-height-p);
    margin: 83px 50px 0px 255px;
    padding: 50px;
    position: relative;
    transition: margin-left .5s ease;

    h1,h2,h3,h4,h5,h6 {
        // reset styles
        color: inherit;
        display: block;
        float: none;
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
        margin-left: 50px;
    }

    @media (max-width: 782px) {
        transition: none;
        margin-left: 50px;
    }
}
</style>
