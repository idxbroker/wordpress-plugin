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
                    <svg-icon :icon="step.icon" />
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
                <idx-button size="lg" theme="light" @click="$emit('back-step')">← Back</idx-button>
                <idx-button size="lg" theme="link" @click="$emit('skip-step')">Skip</idx-button>
                <idx-button size="lg" @click="$emit('continue')">Continue</idx-button>
            </idx-block>
        </idx-block>
    </idx-block>
</template>
<script>
import RelatedLinks from '@/components/RelatedLinks.vue'
import SvgIcon from '@/components/SvgIcon.vue'
export default {
    name: 'ContentCard',
    components: {
        RelatedLinks,
        SvgIcon
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
        --space-button: 16px;
        --content-margin: var(--space-8) var(--space-8) 0;
        --footer-margin: var(--space-8);
        --footer-padding: var(--space-8) 0 var(--space-8);
        --sidebar-margin: var(--space-8) var(--space-8) 0;
        background-color: $white;
        color: $gray-875;
        display: grid;
        font-size: var(--font-size-p);
        height: 100%;
        line-height: var(--line-height-p);
        grid-template-areas:
            "header"
            "content"
            "sidebar"
            "footer";

        &__buttons {
            display: flex;
            gap: var(--space-button);
        }

        &__content {
            grid-area: content;
            margin: var(--content-margin);
            overflow-y: auto;

            h1 {
                color: inherit;
                display: block;
                font-size: var(--font-size-h1);
                font-weight: 300;
                line-height: var(--line-height-h1);
                margin-bottom: var(--space-4);
            }

            p {
                font-size: inherit;
                line-height: inherit;
                margin-bottom: var(--space-6);
                max-width: 45em;
            }
        }

        &__footer {
            border-top: 2px solid $gray-250;
            grid-area: footer;
            margin: var(--footer-margin);
            padding: var(--footer-padding);

            .btn:first-of-type {
                margin-right: auto;
            }
        }

        &__sidebar {
            grid-area: sidebar;
            margin: var(--sidebar-margin);

            .card-header {
                border-bottom: 0 none;
                line-height: var(--space-5);
            }
        }

        &__stepper {
            border-bottom: 2px solid $gray-250;
            display: flex;
            grid-area: header;
            justify-content: center;
            padding: var(--space-8);

            .icon-users {
                width:20px;
            }
        }

        @media only screen and (min-width: 960px)   {
            --content-margin: var(--space-10) var(--space-8) var(--space-9) var(--space-15);
            --footer-margin: 0 var(--space-12);
            --footer-padding: var(--space-8) 0 var(--space-8);
            --sidebar-margin: var(--space-10) var(--space-9) var(--space-15) 0;
            grid-template-columns: 1fr 1fr 360px;
            grid-template-rows: auto 1fr auto;
            grid-template-areas:
                "header  header  header"
                "content content sidebar"
                "footer  footer  footer";
        }
    }
</style>
