/* eslint-disable object-property-newline */
import { applyIdToArray } from '@utilityPath/GenerateId.js'
import { AGENTS, API_KEY, LISTINGS, SOCIAL_PRO } from '@/data/productTerms'

const _navLinks = [
    {
        label: 'Get Started',
        icon: 'wizard',
        collapsed: true,
        link: '/guided-setup/welcome'
    },
    {
        label: 'Import',
        icon: 'cloud',
        collapsed: true,
        requires: [AGENTS, LISTINGS],
        routes: [
            { label: 'Agents', link: '/import/agents/', requires: [AGENTS] },
            { label: 'Listings', link: '/import/listings/', requires: [LISTINGS] }
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
            { label: 'Social Pro', link: '/settings/social-pro', requires: [API_KEY, SOCIAL_PRO] },
            { label: 'Google My Business', link: '/settings/gmb', requires: [LISTINGS] }
        ]
    },
    {
        label: 'Support',
        icon: 'flag',
        collapsed: true,
        routes: [
            { label: 'FAQ', link: '#faq-tbd' },
            { label: 'Knowledgebase', link: 'https://support.idxbroker.com/s/' },
            { label: 'IDX Support', link: 'https://support.idxbroker.com/s/contactsupport' }
        ]
    }
]

export const navLinks = applyIdToArray(_navLinks, 'itemId', 'routes')
/* eslint-enable object-property-newline */
