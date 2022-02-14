<template>
    <idx-block :className="{
            'content-page': true,
            'content-page--slide-left': !expanded
        }"
    >
        <SvgSprite/>
        <notification :notification="notification"/>
        <slot></slot>
    </idx-block>
</template>
<script>
import { mapState } from 'vuex'
import Notification from '@/components/Notification.vue'
import SvgSprite from '@/components/SvgSprite.vue'
export default {
    name: 'page',
    components: {
        Notification,
        SvgSprite
    },
    computed: {
        ...mapState({
            expanded: state => state.routes.expanded,
            notification: state => state.alerts.notification
        })
    }
}
</script>

<style lang="scss">
@import '@/styles/formContentStyles.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons';
.content-page {
    font-size: var(--font-size-p);
    line-height: var(--line-height-p);
    margin: 135px 30px 0 300px;
    position: relative;
    transition: margin-left .5s ease;

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

    > .notification {
        margin-bottom: 50px;
    }

    > .section {
        background-color: $white;
        padding: 50px;
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
