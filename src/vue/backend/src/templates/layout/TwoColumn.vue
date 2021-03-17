<template>
    <idx-block :id="id" :className="className">
        <idx-block tag="main" className="section__content">
            <h1>{{ title }}</h1>
            <slot />
        </idx-block>
        <idx-block tag="aside" v-if="$slots.related" className="section__content">
            <slot name="related" />
        </idx-block>
    </idx-block>
</template>
<script>
export default {
    name: 'two-column',
    props: {
        title: {
            type: String,
            required: true
        }
    },
    computed: {
        id () {
            return this.title.toLowerCase().split(' ').join('-')
        },
        className () {
            return {
                section: true,
                'section--two-column': !!this.$slots.related
            }
        }
    }
}
</script>
<style lang="scss">
.section {
    &--two-column {
        display: grid;
        grid-template-columns: minmax(50%, 1fr) minmax(auto, 300px);
        grid-template-rows: auto;
        grid-gap: 4rem;
        @media (max-width: 782px) {
            grid-template-columns: 100%;
        }
    }
    &__content h1 {
        font-size: var(--font-size-h1);
        font-weight: 300;
        line-height: var(--line-height-h1);
        margin-bottom: var(--space-10);
    }
}
</style>
