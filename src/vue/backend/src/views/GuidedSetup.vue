<template>
    <idx-fullscreen customClass="guided-intro">
        <idx-container fluid>
            <idx-block className="hero">
                <h1>Welcome to IMPress for IDX Broker</h1>
                <p>Add some description about how we will guide the user through setting up IMPress</p>
            </idx-block>
            <idx-block className="media">
                <idx-block className="media-image">
                    <img src="@/assets/guided-setup.svg" alt="Illustration of monitor and mobile device" loading="lazy">
                </idx-block>
                <idx-block className="media-content">
                    <h2>What this guide covers</h2>
                    <idx-block>Cover the bases paragraph on what the install covers.</idx-block>
                    <idx-block>Lorem ipsum dolor sit amet,</idx-block>
                    <idx-list>
                        <idx-list-item>Bullet list item 1</idx-list-item>
                        <idx-list-item>Bullet list item 2</idx-list-item>
                        <idx-list-item>Bullet list item 3</idx-list-item>
                    </idx-list>
                    <idx-button tag="a" href="#" size="lg">Let's Get Started</idx-button>
                </idx-block>
            </idx-block>
        </idx-container>
    </idx-fullscreen>
</template>

<style scoped lang="scss">
    @import '~@idxbrokerllc/idxstrap/dist/styles/globalVariables.scss';
    @import '~@idxbrokerllc/idxstrap/dist/styles/base.scss';
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons';
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/fullscreen';
    @import '~bootstrap/scss/grid';

    .guided-intro {
        --font-size-h1: 31px;
        --font-size-h2: 25px;
        --font-size-p: 16px;
        --font-size-p-large: 18px;
        --line-height-h1: 28px;
        --line-height-h2: 28px;
        --line-height-p: 28px;
        --line-height-p-large: 28px;
        --space-4: 16px;
        --space-6: 24px;
        --space-9: 36px;
        --space-10: 40px;
        align-items: center;
        display: flex;

        .container-fluid {
            align-items: center;
            display: flex;
            flex-direction: column;
        }
    }

    .hero {
        font-size: var(--font-size-p-large);
        line-height: var(--line-height-p-large);
        margin-bottom: 40px;
        text-align: center;

        h1 {
            font-size: var(--font-size-h1);
            line-height: var(--line-height-h1);
            margin-bottom: var(--space-4);
            text-transform: uppercase;
        }
    }

    .media {
        font-size: var(--font-size-p-large);
        line-height: var(--line-height-p-large);
        max-width: fit-content;
    }

    .media-content {
        background-color: #fff;
        box-shadow: 10px 10px 0px #00000019;
        color: $gray-800;
        padding: var(--space-6) var(--space-9);

        h2 {
            font-size: var(--font-size-h2);
            line-height: var(--line-height-h2);
            margin-bottom: var(--space-4);
            text-align: center;
        }

        .btn {
            display: block;
            margin: var(--space-6) auto 0;
            width: fit-content;
        }
    }

    .media-image {
        text-align: center;

        img {
            height: 312px;
        }
    }

    @media screen and (max-width: 1199px) {
        .media-image {
            margin-bottom: 40px;
        }
    }

    @media screen and (min-width: 1200px) {
        .hero {
            margin-bottom: 158px;
        }
        .media {
            display: grid;
            grid-gap: 70px;
            grid-template-columns: auto 1fr;
        }
    }
</style>
