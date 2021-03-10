<template>
    <idx-container fluid customClass="guided-setup__welcome">
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
                <idx-button @click="startSetup" size="lg">Let's Get Started</idx-button>
            </idx-block>
        </idx-block>
    </idx-container>
</template>
<script>
export default {
    name: 'guided-setup-welcome',
    methods: {
        startSetup () {
            this.$router.push({ path: '/guided-setup/connect/api' })
        }
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/fullscreen';
.guided-setup__welcome {
    padding: 50px;
    background-color: $cyan;
    color: $white;
}
.gs {
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

    @media screen and (min-width: 1250px) {

        &__hero {
            margin-bottom: 158px;
        }

        &__media {
            display: flex;
            justify-content: space-between;
            grid-gap: var(--space-18);
        }
    }
}
</style>
