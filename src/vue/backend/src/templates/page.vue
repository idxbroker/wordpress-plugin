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
@import '~bootstrap/scss/forms';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider.scss';
@import '../styles/formContentStyles.scss';
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
  --wordpress-header-footer-height: 72px;
    background-color: $white;
    font-size: var(--font-size-p);
    height: calc(100vh - var(--header-height) - var(--wordpress-header-footer-height));
    line-height: var(--line-height-p);
    margin: var(--header-height) 0 0px $v-nav-width;
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
