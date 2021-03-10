<template>
    <idx-block className="feedback">
        <idx-block className="feedback__icon">
            <svg-icon icon="exclamation-triangle" />
        </idx-block>
        <idx-block tag="h3" className="feedback__title">{{ title }}</idx-block>
        <slot name="content"></slot>
        <idx-button tag="a" outline :href="link">Show Me</idx-button>
    </idx-block>
</template>

<script>
import SvgIcon from '@/components/SvgIcon.vue'
export default {
    name: 'Feedback',
    components: {
        SvgIcon
    },
    props: {
        title: {
            type: String,
            default: ''
        },
        link: {
            type: String,
            default: '',
            required: true
        }
    }
}
</script>

<style scoped lang="scss">
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons';

    .feedback {
        align-items: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        margin-left: auto;
        margin-right: auto;
        max-width: 600px;
        text-align: center;
        &__icon {
            background-color: $primary;
            border-radius: var(--space-4);
            color: $white;
            font-size: 64px;
            margin-bottom: var(--space-10);
            padding: var(--space-10);
        }
        &__title {
            color: $gray-800;
            font-size: var(--font-size-h3);
            letter-spacing: var(--letter-spacing-h3);
            line-height: var(--line-height-h3);
            margin-bottom: var(--space-4);
            text-transform: uppercase;
        }
        p {
            font-size: var(--font-size-p-large);
            line-height: var(--line-height-p-large);
            margin-bottom: var(--space-6);
        }
    }
</style>
