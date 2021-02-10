<template>
    <idx-block :className="{app: isActive, 'nav-is-collapsed': navCollapsed}">
        <Header>
            <template v-slot:toggle>
                <idx-button
                    @click="toggleCollapse()"
                    customClass="toggle-nav"
                    :aria-expanded="navCollapsed ? 'false' : 'true'">
                    <img src="@/assets/arrow-right-light.svg" height="30" alt="IDX Broker" loading="lazy">
                    Toggle</idx-button>
            </template>
        </Header>
        <Navigation>
            <idx-block className="nav">
                <router-link to="/settings">
                    <idx-block tag="span" className="link-icon">(icon)</idx-block>
                    <idx-block tag="span" className="link-text">Settings</idx-block>
                </router-link>
            </idx-block>
        </Navigation>
        <Page>
            <router-view/>
        </Page>
    </idx-block>
</template>

<script>
import Header from '@/components/Header.vue'
import Navigation from '@/components/Navigation.vue'
import Page from '@/components/Page.vue'

export default {
    components: {
        Header,
        Navigation,
        Page
    },
    data () {
        return {
            isActive: true,
            navCollapsed: false
        }
    },
    methods: {
        toggleCollapse (el) {
            this.navCollapsed = !this.navCollapsed
        }
    },
    computed: {
    },
    created () {
    }
}
</script>

<style lang="scss">
    @import '~@idxbrokerllc/idxstrap/dist/styles/base.scss';
    @import '~bootstrap/scss/grid';

    html,
    body {
        height: 100%;
        width: 100%;
    }

    .app {
        background-color: #fff;
        display: grid;
        height: 100%;
        width: 100%;

        grid-template-areas:
            "header"
            "sidebar"
            "content"
    }

    .navigation {

        a {
            color: #fff;
            display: flex;
            letter-spacing: 1px;

            span {
                padding: 12px 8px;
            }
        }
    }

    @media only screen and (min-width: 500px)  {
        .app {
            grid-template-columns: min-content auto;
            grid-template-rows: 80px auto;
            grid-template-areas:
                "header   header"
                "sidebar  content";

            .navigation {
                width: 33.34%;
            }

            .toggle-nav img {}

            &.nav-is-collapsed .navigation {
                width: 50px;

                .link-text {
                    display: none;
                }
            }
        }

        .app:not(.nav-is-collapsed) .toggle-nav img {
            transform: rotate(180deg);
        }
    }

    @media only screen and (min-width: 900px)   {
        .app {
            grid-template-areas:
                "header  header  header"
                "sidebar content content";

            .navigation {
                width: 255px;
            }
        }
    }
</style>
