/**
 * Route Meta
 * Uses:
 * Use when passing route information as props to a route component, while simultaneously passing to route.children.
 */
export default {
    settings: {
        listings: [{
            path: '',
            name: 'IMPress Listings General Settings',
            label: 'General',
            component: () => import('@/views/children/generalContentTab')
        },
        {
            path: 'idx',
            name: 'IMPress Listings IDX Settings',
            label: 'IDX',
            component: () => import('@/views/children/idxContentTab')
        },
        {
            path: 'advanced',
            name: 'IMPress Listings Advanced Settings',
            label: 'Advanced',
            component: () => import('@/views/children/advancedContentTab')
        }]
    },
    imports: {
        listings: [
            {
                path: '',
                name: 'Unimported IDX Listings',
                label: 'Unimported',
                component: () => import('@/views/children/unimportedListings')
            },
            {
                path: 'imported',
                name: 'Imported IDX Listings',
                label: 'Imported',
                component: () => import('@/views/children/importedListings')
            }
        ],
        agents: [
            {
                path: '',
                name: 'Unimported IDX Agents',
                label: 'Unimported',
                component: () => import('@/views/children/unimportedAgents')
            },
            {
                path: 'imported',
                name: 'Imported IDX Agents',
                label: 'Imported',
                component: () => import('@/views/children/importedAgents')
            }
        ]
    }
}
