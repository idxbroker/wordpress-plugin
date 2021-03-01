<template>
    <idx-fullscreen customClass="gs">
        <idx-container fluid>
            <idx-block className="gs__hero">
                <h1>Welcome to IMPress for IDX Broker</h1>
                <p>Add some description about how we will guide the user through setting up IMPress</p>
            </idx-block>
            <idx-block className="gs__media">
                <idx-block className="gs__media-image">
                    <img src="@/assets/guided-setup.svg" alt="Illustration of monitor and mobile device" loading="lazy">
                </idx-block>
                <idx-block className="gs__media-content">
                    <h2>What this guide covers</h2>
                    <idx-block>Cover the bases paragraph on what the install covers.</idx-block>
                    <idx-block>Lorem ipsum dolor sit amet,</idx-block>
                    <idx-list>
                        <idx-list-item>Bullet list item 1</idx-list-item>
                        <idx-list-item>Bullet list item 2</idx-list-item>
                        <idx-list-item>Bullet list item 3</idx-list-item>
                    </idx-list>
                    <idx-button href="#" size="lg">Let's Get Started</idx-button>
                </idx-block>
            </idx-block>
        </idx-container>
    </idx-fullscreen>
</template>

<script>
export default {
    name: 'GuidedSetup'
}
</script>

<style lang="scss">
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons';
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/fullscreen';

    .gs {
        align-items: center;
        bottom: var(--space-9);
        display: flex;
        height: auto;
        left: var(--space-9);
        min-height: 800px;
        position: relative;
        right: var(--space-9);
        top: var(--space-9);
        width: auto;

        &.fullscreen {
            position: absolute;
            z-index: 1;
        }

        .container-fluid {
            align-items: center;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        &__hero {
            font-size: var(--font-size-p-large);
            line-height: var(--line-height-p-large);
            margin-bottom: var(--space-10);
            text-align: center;

            h1 {
                margin-bottom: var(--space-4);
                text-transform: uppercase;
            }
        }

        &__media {
            font-size: var(--font-size-p-large);
            line-height: var(--line-height-p-large);
            max-width: fit-content;
        }

        &__media-content {
            background-color: $white;
            box-shadow: 10px 10px 0px #00000019;
            color: $gray-800;
            padding: var(--space-6) var(--space-9);

            h2 {
                margin-bottom: var(--space-4);
                text-align: center;
            }

            .btn {
                display: block;
                margin: var(--space-6) auto 0;
                width: fit-content;
            }
        }

        &__media-image {
            text-align: center;

            img {
                height: 312px;
            }
        }

        @media screen and (max-width: 1549px) {

            &__media-image {
                margin-bottom: var(--space-10);
            }
        }

        @media screen and (min-width: 1550px) {

            &__hero {
                margin-bottom: 158px;
            }

            &__media {
                display: grid;
                grid-gap: var(--space-18);
                grid-template-columns: auto 1fr;
            }
        }
    }
</style>
