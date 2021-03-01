<template>
    <idx-block :id="id" :className="className">
        <idx-block className="section__content">
            <h1>{{ title }}</h1>
            <slot />
        </idx-block>
        <idx-block v-if="$slots.related" className="section__content">
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
        grid-template-columns: 1fr 300px;
        grid-template-rows: auto;
        grid-gap: 4rem;
    }
    &__content h1 {
        margin-bottom: var(--space-10);
    }
}
</style>
