<template>
    <idx-card>
        <idx-card-header>{{ title }}</idx-card-header>
        <idx-card-body>
            <idx-list unstyled>
                <idx-list-item v-for="link in relatedLinks" :key="link.href">
                    <a :href="link.href" target="_blank">{{ link.text }}</a>
                </idx-list-item>
                <idx-list-item>
                    <a href="https://middleware.idxbroker.com/mgmt/" target="_blank">IDX Broker Middleware</a>
                </idx-list-item>
                <idx-list-item>
                    <a href="https://www.idxbroker.com/" target="_blank">Sign up for IDX Broker</a>
                </idx-list-item>
            </idx-list>
        </idx-card-body>
    </idx-card>
</template>

<script>
export default {
    name: 'RelatedLinks',
    props: {
        title: {
            type: String,
            default: 'Related Links'
        },
        relatedLinks: {
            type: Array,
            default: () => []
        }
    }
}
</script>

<style scoped lang="scss">
    @import '~@idxbrokerllc/idxstrap/dist/styles/globalVariables.scss';
    @import '~@idxbrokerllc/idxstrap/dist/styles/base.scss';
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/card';
    @import '~bootstrap/scss/list-group';

    .card {
        border: 1px solid $gray-250;
        max-width: 300px;
        min-height: 226px;
        padding: var(--space-6);

        .card-body {
            padding: 0;
        }

        .card-header {
            color: $gray-400;
            margin-bottom: var(--space-5);
            padding: 0;
        }
    }
</style>
