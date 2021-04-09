<template>
    <idx-dialog :show="true" @dismiss="closeDialog" customClass="gs-dialog">
        <template v-slot:header>
            <idx-block className="dialog-header">
                <idx-block className="dialog-header__title">{{ title }}</idx-block>
                <idx-block className="dialog-header__dismiss">
                    <span @click="closeDialog">Close ×</span>
                </idx-block>
            </idx-block>
        </template>
        <ContentCard @back-step="$emit('back-step')" @skip-step="$emit('skip-step')" @continue="$emit('continue')" :steps="steps" :cardTitle="cardTitle" :relatedLinks="relatedLinks">
            <template v-slot:description>
                <slot name="description"></slot>
            </template>
            <template v-slot:controls>
                <slot name="controls"></slot>
            </template>
        </ContentCard>
    </idx-dialog>
</template>

<script>
import ContentCard from '@/components/ContentCard.vue'
import { mapActions, mapGetters } from 'vuex'
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
            title: 'IMPress for IDX Broker Setup'
        }
    },
    computed: {
        ...mapGetters({
            changedModules: 'guidedSetup/changedModules'
        })
    },
    methods: {
        ...mapActions({
            setItem: 'guidedSetup/setItem'
        }),
        closeDialog () {
            for (let x = 0; x < this.changedModules.length; x++) {
                this.setItem({
                    key: this.changedModules[x].module,
                    value: {
                        changes: {},
                        module: this.changedModules[x].module,
                        path: this.changedModules[x].path
                    }
                })
            }
            this.showDialog = false
            this.$router.push({ path: '/settings/general' }, () => {
                location.reload()
            })
        }
    }
}
</script>

<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/dialog';

.dialog__mask {
    --dialog-header-height: 48px;
    --dialog-wrapper-space: 60px;
    z-index: 99999;
}

.gs-dialog .dialog__container {
    height: calc(100vh - var(--dialog-header-height) - var(--dialog-wrapper-space) * 2);
    max-width: 1030px;
}

.dialog-header {
    align-items: center;
    background: $gray-800;
    color: $white;
    display: flex;
    height: var(--dialog-header-height);
    justify-content: space-between;
    letter-spacing: 1.6px;
    padding: 0 var(--space-4);
    text-transform: uppercase;
}

.dialog-header__title {
    font-weight: 400;
}

@media (min-width: 576px) {
    .gs-dialog .dialog__container {
        width: 100%;
    }

    .gs-dialog .dialog__wrapper {
        margin-top: var(--space-15);
    }
}
@media (max-width: 960px) {
    .gs-dialog .dialog__container {
        height: 100%;
        .progress-stepper__container {
            text-align: center;
        }
    }
}
</style>
