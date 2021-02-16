<template>
    <idx-dialog :show="showDialog" @dismiss="closeDialog" customClass="gs-dialog">
        <template v-slot:header>
            <idx-block className="dialog-header">
                <idx-block className="dialog-header__title">{{ title }}</idx-block>
                <idx-block className="dialog-header__dismiss">
                    <span @click="closeDialog">Close X</span>
                </idx-block>
            </idx-block>
        </template>
        <ContentCard :steps="steps" :cardTitle="cardTitle" :relatedLinks="relatedLinks">
            <template v-slot:description></template>
            <template v-slot:controls></template>
        </ContentCard>
    </idx-dialog>
</template>

<script>
import ContentCard from '@/components/ContentCard.vue'
export default {
    name: 'GuidedSetupContentCard',
    components: {
        ContentCard
    },
    props: {
        cardTitle: {
            type: String,
            default: ''
        },
        relatedLinks: {
            type: Array,
            default: () => []
        },
        steps: {
            type: Array,
            default: () => []
        }
    },
    data () {
        return {
            showDialog: true,
            title: 'IMPress for IDX Broker Setup'
        }
    },
    methods: {
        openDialog () {
            this.showDialog = true
        },
        closeDialog () {
            this.showDialog = false
        }
    }
}
</script>

<style lang="scss">

    .gs .dialog__mask {
        position: absolute;
    }

    .gs-dialog .dialog__container {
        max-width: 1030px;
    }

    .dialog-header {
        align-items: center;
        background: $gray-800;
        color: $white;
        display: flex;
        height: var(--space-12);
        justify-content: space-between;
        letter-spacing: 1.6px;
        padding: 0 var(--space-4);
        text-transform: uppercase;
    }

    .dialog-header__title {
        font-weight: 500;
    }

    @media (min-width: 576px) {

        .gs-dialog .dialog__container {
            width: 100%;
        }

        .gs-dialog .dialog__wrapper {
            margin-top: var(--space-15);
        }
    }
</style>
