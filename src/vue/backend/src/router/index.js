import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

const routes = [
    {
        path: '/import',
        children: [
            {
                path: '/listings',
                children: [
                    {
                        path: 'imported',
                        name: 'Imported IDX Listings'
                        // component
                    },
                    {
                        path: '',
                        name: 'Unimported IDX Listings'
                        // component
                    }
                ]
            },
            {
                path: '/agents',
                children: [
                    {
                        path: 'imported',
                        name: 'Imported Agents'
                        // component
                    },
                    {
                        path: '',
                        name: 'Unimported Agents'
                        // component
                    }
                ]
            }
        ]
    },
    {
        path: '/settings',
        name: 'Settings',
        children: [
            {
                path: 'general',
                name: 'IMPress General Settings'
                // component
            },
            {
                path: 'omnibar',
                name: 'IMPress Omnibar Settings'
                // component
            },
            {
                path: 'listings',
                children: [
                    {
                        path: '',
                        name: 'IMPress Listings General Settings'
                        // component
                    },
                    {
                        path: '',
                        name: 'IMPress Listings IDX Settings'
                        // component
                    },
                    {
                        path: '',
                        name: 'IMPress Listings Advanced Settings'
                        // component
                    }
                ]
            },
            {
                path: 'agents',
                name: 'IMPress Agents Settings'
                // component
            },
            {
                path: 'social-pro',
                name: 'Social Pro Settings'
                // component
            }
        ]
    },
    {
        path: '/guided-setup',
        name: 'Guided Setup',
        // component
        children: [
            {
                path: 'connect',
                children: [
                    {
                        path: 'api',
                        name: 'Connect Account'
                        // component
                    },
                    {
                        path: 'general',
                        name: 'General Settings'
                        // component
                    },
                    {
                        path: 'omnibar',
                        name: 'Omnibar Settings'
                        // component
                    }
                ]
            },
            {
                path: 'listings',
                children: [
                    {
                        path: 'activate',
                        name: 'Activate IMPress Listings'
                        // component
                    },
                    {
                        path: 'general',
                        name: 'Configure General IMPress Listings'
                        // component
                    },
                    {
                        path: 'idx',
                        name: 'Configure IDX IMPress Listings'
                        // component
                    },
                    {
                        path: 'advanced',
                        name: 'Configure Advanced IMPress Listings'
                        // component
                    }
                ]
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
    }
]

const router = new VueRouter({
    routes
})

export default router
