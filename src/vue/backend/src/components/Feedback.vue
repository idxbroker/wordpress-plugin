<template>
    <idx-block className="feedback">
        <idx-block className="feedback__icon">
            <!-- TODO: Waiting on new icon -->
        </idx-block>
        <idx-block tag="h2" className="feedback__title">{{ title }}</idx-block>
        <slot name="content"></slot>
        <idx-button tag="a" outline :href="link">Show Me</idx-button>
    </idx-block>
</template>

<script>
export default {
    name: 'RelatedLinks',
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
    @import '~@idxbrokerllc/idxstrap/dist/styles/globalVariables.scss';
    @import '~@idxbrokerllc/idxstrap/dist/styles/base.scss';
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons';

    :root {
        --font-family: 'Mulish', "Segoe UI", "Helvetica Neue", Arial, sans-serif;
        --font-size-h3: 21px;
        --font-size-p-large: 18px;
        --letter-spacing-h3: 2.1px;
        --line-height-h3: 22px;
        --line-height-p-large: 28px;
        --space-3: 12px;
        --space-4: 16px;
        --space-6: 24px;
        --space-8: 32px;
        --space-10: 40px;
    }

    .feedback {
        align-items: center;
        display: flex;
        flex-direction: column;
        font-family: var(--font-family);
        justify-content: center;
        margin-left: auto;
        margin-right: auto;
        max-width: 600px;
        text-align: center;
        &__icon {
            background-color: $primary;
            border-radius: var(--space-4);
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
