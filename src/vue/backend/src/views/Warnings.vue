<template>
    <div class="home">
        <div style="margin-bottom:var(--space-8)">
            <Feedback title="Api key required" link="http://localhost:8123/#/">
                <p>To import your listings, you need to <a href="#">connect IMPress for IDX Broker</a> to your IDX Broker account by adding your API key.</p>
            </Feedback>
        </div>
        <div style="margin-bottom:var(--space-8)">
            <Feedback title="Api key required" link="http://localhost:8123/#/">
                <p>To import your agents, you need to <a href="#">connect IMPress for IDX Broker</a> to your IDX Broker account by adding your API key.</p>
            </Feedback>
        </div>
        <div style="margin-bottom:var(--space-8)">
            <Feedback title="IMPress listings is disabled" link="http://localhost:8123/#/">
                <p>To import your listings, you need to <a href="#">enable IMPress Listings</a> and ensure that your API key is active.</p>
            </Feedback>
        </div>
        <div style="margin-bottom:var(--space-8)">
            <Feedback title="IMPress agents is disabled" link="http://localhost:8123/#/">
                <p>To import your agents, you need to <a href="#">enable IMPress Agents</a> and ensure that your API key is active.</p>
            </Feedback>
        </div>
    </div>
</template>

<script>

import Feedback from '@/components/Feedback.vue'

export default {
    name: 'Warnings',
    components: {
        Feedback
    }
}
</script>
