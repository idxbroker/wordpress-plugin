<template>
    <idx-block className="content-card">
        <idx-block className="content-card__stepper">
            <idx-progress-stepper
                v-for="step in steps"
                :key="step.name"
                v-bind="{
                    ...step
                }"
            >
                <template v-slot:icon>
                    <idx-icon :icon="step.icon"></idx-icon>
                </template>
            </idx-progress-stepper>
        </idx-block>
        <idx-block
            className="content-card__content"
            role="tabpanel">
            <h1>{{ cardTitle }}</h1>
            <slot name="description"></slot>
            <slot name="controls"></slot>
        </idx-block>
        <idx-block className="content-card__sidebar">
            <RelatedLinks :relatedLinks="relatedLinks"/>
        </idx-block>
        <idx-block className="content-card__footer">
            <idx-block className="content-card__buttons">
                <idx-button href="#" size="lg" theme="light" @click="$emit('back-step')">← Back</idx-button>
                <idx-button href="#" size="lg" theme="link" @click="$emit('skip-step')">Skip</idx-button>
                <idx-button href="#" size="lg" @click="$emit('continue')">Continue</idx-button>
            </idx-block>
        </idx-block>
    </idx-block>
</template>
<script>
import RelatedLinks from '@/components/RelatedLinks.vue'
export default {
    name: 'ContentCard',
    components: {
        RelatedLinks
    },
    props: {
        cardTitle: {
            type: String,
            default: ''
        },
        steps: {
            type: Array,
            default: () => []
        },
        relatedLinks: {
            type: Array,
            default: () => []
        }
    }
}
</script>

<style lang="scss">
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/vNav';
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/progressStepper';
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/progressBar';

    .content-card {
        --space-button: 8px;
        background-color: $white;
        color: $gray-875;
        display: grid;

        grid-template-areas:
            "header"
            "content"
            "sidebar"
            "footer"
    }

    .content-card__buttons {
        display: flex;
        margin-left: calc(-1 * var(--space-button));
        margin-right: calc(-1 * var(--space-button));
    }

    .content-card__content {
        grid-area: content;
        margin: var(--space-8) var(--space-8) 0;

        h1 {
            margin-bottom: var(--space-4);
        }

        p {
            margin-bottom: var(--space-4);
            max-width: 45em;
        }
    }

    .content-card__footer {
        border-top: 2px solid $gray-250;
        grid-area: footer;
        margin: var(--space-8);
        padding: var(--space-8) 0 var(--space-15);

        .btn {
            margin: var(--space-button);
        }

        .btn:first-of-type {
            margin-right: auto;
        }
    }

    .content-card__sidebar {
        grid-area: sidebar;
        margin: 0 var(--space-8);
    }

    .content-card__stepper {
        border-bottom: 2px solid $gray-250;
        display: flex;
        grid-area: header;
        justify-content: center;
        padding: var(--space-8);
    }

    @media only screen and (min-width: 1200px)   {

        .content-card {
            grid-template-columns: 1fr 1fr 360px;
            grid-template-rows: auto 1fr auto;
            grid-template-areas:
                "header  header  header"
                "content content sidebar"
                "footer  footer  footer";
        }

        .content-card__content {
            margin: var(--space-10) var(--space-8) var(--space-15) var(--space-15);
        }

        .content-card__footer {
            margin: 0 var(--space-12);
            padding: var(--space-8) 0 var(--space-15);
        }

        .content-card__sidebar {
            margin: var(--space-10) var(--space-15) var(--space-15) 0;
        }
    }
</style>
