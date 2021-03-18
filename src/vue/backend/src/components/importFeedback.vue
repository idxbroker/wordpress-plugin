<template>
    <idx-block className="feedback">
        <idx-block className="feedback__icon">
            <svg-icon icon="exclamation-triangle" />
        </idx-block>
        <idx-block tag="h3" className="feedback__title">{{ title }}</idx-block>
            <span v-if="missingAPI">
                {{ content.startingStatement }}
                <idx-block
                    className="warning__link"
                    tag="span"
                    tabindex="0"
                    role="link"
                    @click="showThem"
                >
                    connect IMPress for IDX Broker
                </idx-block>
                <br>
                to your IDX Broker account by adding your API key
            </span>
            <span v-else>
                {{ content.startingStatement }}
                <idx-block
                    className="warning__link"
                    tag="span"
                    tabindex="0"
                    role="link"
                    @click="showThem"
                >
                    {{ content.warningLink }}
                </idx-block>
                <br>
                {{ content.closingStatement }}
            </span>
        <idx-button tag="a" customClass="feedback__action" outline @click="showThem">Show Me</idx-button>
    </idx-block>
</template>

<script>
import SvgIcon from '@/components/SvgIcon.vue'
export default {
    name: 'import-feedback',
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
        },
        content: {
            type: Object,
            default: () => {}
        },
        missingAPI: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        showThem () {
            const path = this.missingAPI ? '/settings/general' : this.link
            this.$router.push({ path })
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
        &__action.btn {
            margin-top: var(--space-form-section);
            color: $cyan;
        }
    }
</style>
