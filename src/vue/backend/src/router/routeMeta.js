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
            // component
            component: () => import('@/templates/ListingsGeneral.vue')
        },
        {
            path: 'idx',
            name: 'IMPress Listings IDX Settings',
            label: 'IDX',
            // component
            component: () => import('@/templates/impressListingsIdxContent')
        },
        {
            path: 'advanced',
            name: 'IMPress Listings Advanced Settings',
            label: 'Advanced',
            // component
            component: () => import('@/templates/impressListingsAdvancedContent')
        }]
    },
    imports: {
        listings: [
            { path: '', name: 'Unimported IDX Listings', label: 'Unimported' /* Component here */ },
            { path: 'imported', name: 'Imported IDX Listings', label: 'Imported' /* Component here */ }
        ],
        agents: [
            { path: '', name: 'Unimported IDX Agents', label: 'Unimported' /* Component here */ },
            { path: 'imported', name: 'Imported IDX Agents', label: 'Imported' /* Component here */ }
        ]
    }
}
