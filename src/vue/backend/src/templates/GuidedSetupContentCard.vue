<template>
    <idx-block className="gs-content-card">
        <idx-block className="gs-content-card__stepper">progress stepper</idx-block>
        <idx-block className="gs-content-card__content">
            <h1>Connect Your IDX Broker Account</h1>
            <p><strong>This step is optional.</strong> A sentence or two about why you should connect IMPress for IDX Broker to your IDX Broker account. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            <APIKey/>
        </idx-block>
        <idx-block className="gs-content-card__sidebar">
            <RelatedLinks title="title"/>
        </idx-block>
        <idx-block className="gs-content-card__footer">
            <idx-block className="gs-content-card__buttons">
                <idx-button href="#" size="lg" theme="light">← Back</idx-button>
                <idx-button href="#" size="lg" theme="link">Skip</idx-button>
                <idx-button href="#" size="lg">Continue</idx-button>
            </idx-block>
        </idx-block>
    </idx-block>
</template>

<script>
import APIKey from '@/components/APIKey.vue'
import RelatedLinks from '@/components/RelatedLinks.vue'
export default {
    name: 'GuidedSetupContentCard',
    components: {
        APIKey,
        RelatedLinks
    },
    data () {
        return {
            links: [
                {
                    href: '#one',
                    text: 'text'
                }
            ]
        }
    }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons';

    .gs-content-card {
        --button-space: 8px;
        --font-size-h1: 31px;
        --font-size-p: 16px;
        --line-height-h1: 28px;
        --line-height-p: 28px;
        --space-4: 16px;
        --space-6: 24px;
        --space-9: 36px;
        --space-10: 40px;
        background-color: $white;
        color: $gray-875;
        display: grid;
        font-size: var(--font-size-p);
        line-height: var(--line-height-p);

        grid-template-areas:
            "header"
            "content"
            "sidebar"
            "footer"
    }

    .gs-content-card__buttons {
        display: flex;
        margin-left: calc(-1 * var(--button-space));
        margin-right: calc(-1 * var(--button-space));
    }

    .gs-content-card__content {
        grid-area: content;
        margin: 30px 30px 0;

        h1 {
            font-size: var(--font-size-h1);
            line-height: var(--line-height-h1);
            margin-bottom: var(--space-4);
        }

        p {
            margin-bottom: var(--space-4);
            max-width: 45em;
        }
    }

    .gs-content-card__footer {
        border-top: 2px solid $gray-250;
        grid-area: footer;
        margin: 30px;
        padding: 30px 0 60px;

        .btn {
            margin: var(--button-space);
        }

        .btn:first-of-type {
            margin-right: auto;
        }
    }

    .gs-content-card__sidebar {
        grid-area: sidebar;
        margin: 0 30px;
    }

    .gs-content-card__stepper {
        border-bottom: 2px solid $gray-250;
        grid-area: header;
    }

    @media only screen and (min-width: 1200px)   {

        .gs-content-card {
            grid-template-columns: 1fr 1fr 360px;
            grid-template-rows: auto 1fr auto;

            grid-template-areas:
                "header  header  header"
                "content content sidebar"
                "footer  footer  footer";
        }

        .gs-content-card__content {
            margin: 30px 30px 60px 60px;
        }

        .gs-content-card__footer {
            margin: 0 52px;
            padding: 30px 0 60px;
        }

        .gs-content-card__sidebar {
            margin: 30px 60px 60px 0;
        }
    }
</style>
