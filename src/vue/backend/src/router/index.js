import Vue from 'vue'
import VueRouter from 'vue-router'
import Generic from '@/templates/layout/Generic'
import Layout from '@/templates/layout/Layout'
import routeMeta from './routeMeta'
import { AGENTS, API_KEY, LISTINGS, SOCIAL_PRO } from '@/data/productTerms'
import store from '../store'
import { filterRequires } from '@/utilities'

Vue.use(VueRouter)

const routes = [
    {
        path: '',
        component: Layout,
        children: [
            {
                path: '/import',
                name: 'Import',
                component: Generic,
                children: [
                    {
                        path: 'listings',
                        name: 'IDX Listings',
                        component: () => import('@/views/import/Listings'),
                        children: routeMeta.imports.listings,
                        props: {
                            parentRoute: '/import/listings',
                            tabbedRoutes: routeMeta.imports.listings,
                            requires: [AGENTS, LISTINGS, API_KEY]
                        }
                    },
                    {
                        path: 'agents',
                        name: 'IDX Agents',
                        component: () => import('@/views/import/Agents'),
                        children: routeMeta.imports.agents,
                        props: {
                            parentRoute: '/import/agents',
                            tabbedRoutes: routeMeta.imports.agents
                        }
                    }
                ]
            },
            {
                path: '/settings',
                name: 'Settings',
                component: Generic,
                children: [
                    {
                        path: 'general',
                        name: 'IMPress General Settings',
                        component: () => import('@/views/settings/General')
                    },
                    {
                        path: 'omnibar',
                        name: 'IMPress Omnibar Settings',
                        component: () => import('@/views/settings/Omnibar')
                    },
                    {
                        path: 'listings',
                        component: () => import('@/views/settings/Listings'),
                        children: routeMeta.settings.listings,
                        meta: {
                            requires: [LISTINGS]
                        },
                        props: {
                            parentRoute: '/settings/listings',
                            tabbedRoutes: routeMeta.settings.listings
                        }
                    },
                    {
                        path: 'agents',
                        name: 'IMPress Agents Settings',
                        component: () => import('@/views/settings/Agents'),
                        meta: {
                            requires: [AGENTS]
                        }
                    },
                    {
                        path: 'social-pro',
                        name: 'Social Pro Settings',
                        component: () => import('@/views/settings/SocialPro'),
                        meta: {
                            requires: [API_KEY, SOCIAL_PRO]
                        }
                    },
                    {
                        path: 'gmb',
                        name: 'Google My Business',
                        component: () => import('@/views/settings/GMB'),
                        meta: {
                            requires: [LISTINGS]
                        }
                    }
                ]
            },
            {
                path: '/guided-setup',
                name: 'Guided Setup',
                component: Generic,
                children: [
                    {
                        path: 'connect/welcome',
                        name: 'Welcome',
                        component: () => import('@/views/guided-setup/Welcome')
                    },
                    {
                        path: 'connect/api',
                        name: 'Connect Account',
                        component: () => import('@/views/guided-setup/Api')
                    },
                    {
                        path: 'connect/general',
                        name: 'General Settings',
                        component: () => import('@/views/guided-setup/General')
                    },
                    {
                        path: 'connect/omnibar',
                        name: 'Omnibar Settings',
                        component: () => import('@/views/guided-setup/Omnibar')
                    },
                    {
                        path: 'listings',
                        name: 'Activate IMPress Listings',
                        component: () => import('@/views/guided-setup/Listings')
                    },
                    {
                        path: 'listings/general',
                        name: 'Configure General IMPress Listings',
                        component: () => import('@/views/guided-setup/ListingsGeneral')
                    },
                    {
                        path: 'listings/idx',
                        name: 'Configure IDX IMPress Listings'
                        // component
                    },
                    {
                        path: 'listings/advanced',
                        name: 'Configure Advanced IMPress Listings'
                        // component
                    },
                    {
                        path: 'agents',
                        children: [
                            {
                                path: 'activate',
                                name: 'Activate IMPress Agents'
                                // component
                            },
                            {
                                path: 'configure',
                                name: 'Configure IMPress Agents'
                                // component
                            }
                        ]
                    },
                    {
                        path: 'social-pro',
                        children: [
                            {
                                path: 'activate',
                                name: 'Activate Social Pro'
                                // component
                            },
                            {
                                path: 'general',
                                name: 'Configure Social Pro'
                                // component
                            }
                        ]
                    }
                ]
            },
            {
                path: '/*',
                name: '404',
                component: () => import('@/views/404')
            }
        ]
    }
]

const router = new VueRouter({
    routes
})

router.beforeEach((to, from, next) => {
    if (to.meta && to.meta.requires && to.meta.strict) {
        const { state } = store
        const { meta } = to
        const result = filterRequires(meta, state)
        if (!result) {
            next({ name: '404' })
        }
    } else {
        next()
    }
})

export default router
