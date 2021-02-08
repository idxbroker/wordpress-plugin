<template>
    <div class="idx-wp-feedback">
        <div class="idx-wp-feedback__icon">
            <font-awesome-icon :icon="['fal', 'exclamation-triangle']" size="5x" inverse />
        </div>
        <h2 class="idx-wp-feedback__title">{{ title }}</h2>
        <slot></slot>
        <idx-button tag="a" outline :href="link">Show Me</idx-button>
    </div>
</template>

<script>
export default {
    name: 'RelatedLinks',
    props: {
        title: String,
        link: String
    }
}
</script>

<style scoped lang="scss">

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
