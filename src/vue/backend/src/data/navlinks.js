/* eslint-disable object-property-newline */
import { applyIdToArray } from '@utilityPath/GenerateId.js'

const _navLinks = [
    {
        label: 'Get Started',
        icon: 'wizard',
        collapsed: true,
        link: '/guided-setup'
    },
    {
        label: 'Import',
        icon: 'cloud',
        collapsed: true,
        routes: [
            { label: 'Agents', link: '/import/agents/' },
            { label: 'Listings', link: '/import/listings' }
        ]
    },
    {
        label: 'Settings',
        icon: 'sliders',
        collapsed: true,
        routes: [
            { label: 'General', link: '/settings/general' },
            { label: 'Omnibar', link: '/settings/omnibar' },
            { label: 'Listings', link: '/settings/listings' },
            { label: 'Agents', link: '/settings/agents' },
            { label: 'Social Pro', link: '/settings/social-pro' },
            { label: 'Google My Business', link: '/settings/gmb' }
        ]
    },
    {
        label: 'Support',
        icon: 'flag',
        collapsed: true,
        routes: [
            { label: 'FAQ', link: '#' },
            { label: 'Knowledgebase', link: '#' },
            { label: 'IDX Support', link: '#' }
        ]
    }
]

export const navLinks = applyIdToArray(_navLinks, 'itemId', 'routes')
/* eslint-enable object-property-newline */
