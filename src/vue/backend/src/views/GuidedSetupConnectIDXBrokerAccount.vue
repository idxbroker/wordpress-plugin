<template>
    <!-- Use the guided set up contact card
    Add the appropriate related links
    Find api key: TBD
    IDX Broker Middleware: https://middleware.idxbroker.com/mgmt/
    Sign up for IDX Broker: TBD
    Add the title and description
    Add the API Key component
    Capture events
    @backStep Close modal
    @skipStep move to Your Listings step
    @continue
    Ping API endpoint: TBD
    move to global wrapper step -->
    <GuidedSetupContentCard class="gs" cardTitle="Connect Your IDX Broker Account" :steps="steps" :relatedLinks="links">
        <template v-slot:description>
            <p>This step is optional. A sentence or two about why you should connect IMPress for IDX Broker to your IDX Broker account. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </template>
        <template v-slot:controls>
            <APIKey/>
        </template>
    </GuidedSetupContentCard>
</template>

<script>
import GuidedSetupContentCard from '@/templates/GuidedSetupContentCard.vue'
import APIKey from '@/components/APIKey.vue'
export default {
    name: 'GuidedSetupConnectIDXBrokerAccount',
    components: {
        APIKey,
        GuidedSetupContentCard
    },
    data () {
        return {
            steps: [
                { name: '1. Connect', icon: 'link', total: 2, active: 1 },
                { name: '2. Your Listings', icon: 'list', total: 2, active: 0 },
                { name: '3. Agents', icon: 'users', total: 2, active: 0 },
                { name: '4. Social', icon: 'thumbs-up', total: 1, active: 0 }
            ],
            links: [
                {
                    text: 'Where can I find my API key?',
                    href: '#where'
                },
                {
                    text: 'IDX Broker Middleware',
                    href: 'https://middleware.idxbroker.com/mgmt/'
                },
                {
                    text: 'Sign up for IDX Broker',
                    href: '#signUp'
                }
            ]
        }
    }
}
</script>

<style lang="scss">
</style>
