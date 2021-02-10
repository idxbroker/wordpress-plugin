<template>
    <idx-block className="app {'nav-is-expanded': expanded}">
        <Header/>
        <Navigation>
            <idx-block className="nav">
                <router-link to="/settings">Settings</router-link>
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
            expanded: true
        }
    },
    methods: {
    },
    computed: {
    },
    created () {
    }
}
</script>

<style lang="scss">
    @import '~@idxbrokerllc/idxstrap/dist/styles/globalVariables.scss';
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

    @media only screen and (min-width: 500px)  {
        .app {
            grid-template-columns: 33.34% auto;
            grid-template-rows: 80px auto;
            grid-template-areas:
                "header   header"
                "sidebar  content";
        }
    }

    @media only screen and (min-width: 900px)   {
        .app {
            grid-template-columns: 255px auto;
            grid-template-areas:
                "header  header  header"
                "sidebar content content";
        }
    }
</style>
