<template>
    <div>
        <idx-navbar type="light" theme="light">
            <idx-v-icon
                :customClass="{
                    'horizontal-navigation__toggle': true,
                    'horizontal-navigation__toggle--expanded': expanded
                }"
                :icon="plus"
                @click.native="horizontalExpand"
            ></idx-v-icon>
            <idx-navbar-brand link="/">
                <img :src="idxBrand" alt="IDX Broker Logo" loading="lazy">
            </idx-navbar-brand>
        </idx-navbar>
        <idx-v-nav
            :customClass="{
                'horizontal-navigation__options-bar': true,
                'horizontal-navigation--collapsed': !expanded,
                'v-nav--full-bar': true
            }"
        >
            <idx-block className="v-nav__icon-bar"></idx-block>
            <idx-nav-list>
                <idx-nav-item
                    v-for="(route, i) in routes"
                    :key="i"
                    v-bind="{
                        itemId: route.itemId,
                        label: route.label,
                        link: route.link || null,
                        icon: icons[route.icon],
                        routes: route.routes,
                        collapsed: route.collapsed
                    }"
                    @collapse="doCollapse"
                    @click.native="topLevelClick(route.itemId, route.routes)"
                    @iconClick="iconBarExpand"
                ></idx-nav-item>
            </idx-nav-list>
        </idx-v-nav>
    </div>
</template>
<script>
import { mapActions, mapGetters, mapState } from 'vuex'
import idxBrand from '../assets/idx-logo.svg'
import plus from '../assets/plus.svg'
import cloud from '../assets/cloud-download.svg'
import flag from '../assets/flag.svg'
import sliders from '../assets/sliders.svg'
import wizard from '../assets/wizard.svg'
export default {
    data () {
        return {
            expanded: true,
            idxBrand,
            plus,
            icons: {
                cloud,
                flag,
                sliders,
                wizard
            }
        }
    },
    watch: {
        $route (newVal, oldVal) {
            if (newVal.matched && Array.isArray(newVal.matched)) {
                this.getRouteByPath(newVal.matched[0].path)
                if (this.handleResize()) {
                    this.expanded = false
                }
            }
        }
    },
    computed: {
        ...mapGetters({
            routes: 'routes/navigationRoutes'
        }),
        ...mapState({
            childRoutes: 'routes/routes'
        })
    },
    methods: {
        ...mapActions({
            gatherRoutes: 'routes/gatherRoutes',
            expandRoute: 'routes/expandRoute',
            collapseRoutes: 'routes/collapseRoutes'
        }),
        handleResize () {
            return window.matchMedia('(max-width: 782px)').matches
        },
        horizontalExpand () {
            this.expanded = !this.expanded
        },
        iconBarExpand (e) {
            this.expanded = true
        },
        doCollapse (e) {
            this.expandRoute(e)
        },
        getRouteByPath (path) {
            const route = this.routes.filter(route => {
                let results = false
                if (route.routes) {
                    const childResults = route.routes.filter(child => child.link === path)
                    if (childResults.length) {
                        results = true
                    }
                }
                return results
            })
            if (route.length && route[0].itemId) {
                this.collapseRoutes()
                this.expandRoute(route[0].itemId)
            }
        },
        topLevelClick (id, routes) {
            if (!routes || !routes.length) {
                this.expandRoute(id)
            }
        }
    },
    created () {
        this.gatherRoutes()
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/vNav';
@import '../styles/verticalNavbar.scss';
@import '~bootstrap/scss/navbar';
@import '~bootstrap/scss/nav';
</style>