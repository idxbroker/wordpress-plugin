<template>
    <idx-container fluid customClass="guided-setup__welcome">
        <idx-block className="gs__hero">
            <h2>Guided Setup</h2>
            <h1>IMPress for IDX Broker</h1>
            <p>
                Your IDX Broker account provides you with the IMPress
                plugin. Once enabled, you can display your listings, agents,
                and social media content directly on your WordPress website.
                Get started with our guided setup or manage plugin components
                independently from the main menu at the left of this screen.
            </p>
        </idx-block>
        <idx-block className="gs__media">
            <idx-block className="gs__media-image">
                <img src="@/assets/guided-setup.svg" alt="Illustration of monitor and mobile device" loading="lazy">
            </idx-block>
            <idx-block className="gs__media-content">
                <h2>What this guide covers</h2>
                <idx-block>
                    In this guide, we’ll explain each of the five steps and walk you through the setup.
                </idx-block>
                <idx-list>
                    <idx-block className="gs__bolded">Here's what to expect:</idx-block>
                    <idx-list-item>Connect Your IDX Broker Account (Optional)</idx-list-item>
                    <idx-list-item>Configure Omnibar Search for Your Site</idx-list-item>
                    <idx-list-item>Enable and Configure IMPress Listings</idx-list-item>
                    <idx-list-item>Enable and Configure IMPress Agents</idx-list-item>
                    <idx-list-item v-if="(this.restrictedByBeta && this.optedInBeta) || !this.restrictedByBeta">Connect to Social Pro</idx-list-item>
                </idx-list>
                <idx-button @click="startSetup" size="lg">Let's Get Started</idx-button>
            </idx-block>
        </idx-block>
    </idx-container>
</template>
<script>
import { mapState } from 'vuex'
export default {
    name: 'guided-setup-welcome',
    methods: {
        startSetup () {
            this.$router.push({ path: '/guided-setup/connect/api' })
        }
    },
    computed: {
        ...mapState({
            restrictedByBeta: state => state.socialPro.restrictedByBeta,
            optedInBeta: state => state.socialPro.optedInBeta
        })
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
        max-width: 1190px;
        text-align: center;

        h1 {
            letter-spacing: 1px;
            margin-bottom: var(--space-4);
            text-transform: uppercase;
        }
        h2 {
            text-transform: uppercase;
            font-weight: 700;
        }
    }

    &__media {
        font-size: var(--font-size-p-large);
        line-height: var(--line-height-p-large);
    }
    &__bolded {
        font-weight: 700;
    }
    &__media-content {
        display: flex;
        flex-direction: column;
        grid-gap: 20px;
        background-color: $white;
        box-shadow: 10px 10px 0px #00000019;
        color: $gray-800;
        padding: var(--space-6) var(--space-9);

        h2 {
            text-transform: capitalize;
            margin-bottom: var(--space-4);
            text-align: center;
        }

        ul {
            list-style-type: disc;
            li {
                margin-left: 22px;
            }
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
}

@media screen and (min-width: 1250px) {
    .guided-setup__welcome {
        align-items: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .gs__media {
        display: flex;
        grid-gap: var(--space-18);
    }
}
</style>
