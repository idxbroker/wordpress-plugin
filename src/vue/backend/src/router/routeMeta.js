import { LISTINGS, AGENTS, API_KEY } from '@/data/productTerms'
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
            component: () => import('@/views/children/generalContentTab'),
            meta: {
                requires: [LISTINGS]
            }
        },
        {
            path: 'idx',
            name: 'IMPress Listings IDX Settings',
            label: 'IDX',
            component: () => import('@/views/children/idxContentTab'),
            meta: {
                requires: [LISTINGS]
            }
        },
        {
            path: 'advanced',
            name: 'IMPress Listings Advanced Settings',
            label: 'Advanced',
            component: () => import('@/views/children/advancedContentTab'),
            meta: {
                requires: [LISTINGS]
            }
        }]
    },
    imports: {
        listings: [
            {
                path: '',
                name: 'Unimported IDX Listings',
                label: 'Unimported',
                /* Component here */
                meta: {
                    requires: [LISTINGS, API_KEY],
                    strict: true
                }
            },
            {
                path: 'imported',
                name: 'Imported IDX Listings',
                label: 'Imported',
                /* Component here */
                meta: {
                    requires: [LISTINGS, API_KEY],
                    strict: true
                }
            }
        ],
        agents: [
            {
                path: '',
                name: 'Unimported IDX Agents',
                label: 'Unimported',
                /* Component here */
                meta: {
                    requires: [AGENTS, API_KEY],
                    strict: true
                }
            },
            {
                path: 'imported',
                name: 'Imported IDX Agents',
                label: 'Imported',
                /* Component here */
                meta: {
                    requires: [AGENTS, API_KEY],
                    strict: true
                }
            }
        ]
    }
}
