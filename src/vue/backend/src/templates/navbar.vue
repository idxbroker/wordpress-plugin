<template>
    <idx-block className="navigation">
        <idx-navbar tag="header" type="light" theme="light" customClass="wordpress-navigation">
            <idx-v-icon
                :customClass="{
                    'horizontal-navigation__toggle': true,
                    'horizontal-navigation__toggle--expanded': expanded
                }"
                :icon="plus"
                @click.native="horizontalExpand"
            ></idx-v-icon>
            <idx-navbar-brand  tag="router-link" to="/guided-setup/welcome">
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
            <idx-nav-list v-if="loadContent">
                <idx-nav-item
                    v-for="(route, i) in routes"
                    :key="i"
                    v-bind="{
                        itemId: route.itemId,
                        label: route.label,
                        linkType: route.linkType || 'router-link',
                        target: route.target || null,
                        link: route.link || null,
                        icon: icons[route.icon],
                        routes: route.routes,
                        collapsed: route.collapsed
                    }"
                    @collapse="doCollapse"
                    @click.native="topLevelClick(route.itemId, route.routes)"
                    @icon-click="iconBarExpand"
                ></idx-nav-item>
            </idx-nav-list>
            <idx-nav-list v-else>
                <idx-skeleton-screen :loading="true" >
                    <template v-slot:skeletonScreen>
                        <idx-block className="sk-grow--75" />
                        <idx-block className="sk-grow--75" />
                        <idx-block className="sk-grow--75" />
                        <idx-block className="sk-grow--75" />
                    </template>
                </idx-skeleton-screen>
            </idx-nav-list>
        </idx-v-nav>
    </idx-block>
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
            this.$nextTick(() => {
                this.routerActivePage()
            })
        },
        loadContent (newVal) {
            if (newVal) {
                this.socialProBeta()
            }
            this.$nextTick(() => {
                this.routerActivePage()
            })
        }
    },
    computed: {
        ...mapGetters({
            routes: 'routes/navigationRoutes'
        }),
        ...mapState({
            childRoutes: state => state.routes.routes,
            expanded: state => state.routes.expanded,
            restrictedByBeta: state => state.socialPro.restrictedByBeta,
            optedInBeta: state => state.socialPro.optedInBeta,
            loadContent: state => state.alerts.loadContent
        })
    },
    methods: {
        ...mapActions({
            gatherRoutes: 'routes/gatherRoutes',
            expandRoute: 'routes/expandRoute',
            collapseRoutes: 'routes/collapseRoutes',
            toggleSidebar: 'routes/toggleSidebar',
            setSocialPro: 'routes/setSocialPro',
            setRoutes: 'routes/setRoutes'
        }),
        handleResize () {
            return window.matchMedia('(max-width: 782px)').matches
        },
        horizontalExpand () {
            this.toggleSidebar({
                key: 'expanded',
                value: !this.expanded
            })
        },
        iconBarExpand (e) {
            this.toggleSidebar({
                key: 'expanded',
                value: true
            })
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
        },
        routerActivePage () {
            const currentPage = `${window.location.hash}`
            this.getRouteByPath(currentPage.substring(1))
            for (const x in this.routes) {
                const routes = this.routes[x].routes
                if (routes) {
                    routes.map(x => {
                        if (currentPage.includes(x.link)) {
                            const activeNavItem = document.querySelector(`.${this.$idxStrap.prefix}v-nav--item a[href="#${x.link}"]`)
                            activeNavItem.classList.add('router-link-exact-active')
                            this.getRouteByPath(x.link)
                        }
                    })
                }
            }
        },
        socialProBeta () {
            if ((this.restrictedByBeta && this.optedInBeta) || !this.restrictedByBeta) {
                let socialPro
                for (const route in this.routes) {
                    if (this.routes[route].routes) {
                        this.routes[route].routes.map(x => {
                            if (x.label === 'Social Pro') {
                                socialPro = x
                            }
                        })
                    }
                }
                this.setSocialPro({ key: socialPro.itemId, value: { ...socialPro, hidden: false } })
            }
        }
    },
    created () {
        this.gatherRoutes()
    },
    mounted () {
        this.$nextTick(() => {
            if (this.loadContent) {
                this.socialProBeta()
                this.routerActivePage()
            }
        })
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/vNav';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/skeletonScreen';
@import '../styles/verticalNavbar.scss';
@import '~bootstrap/scss/navbar';
@import '~bootstrap/scss/nav';
.sk-grow--75 {
    margin-left: 50px;
    background-color: $gray-400;
    &:first-child {
        margin-top: 15px;
    }
}
</style>
