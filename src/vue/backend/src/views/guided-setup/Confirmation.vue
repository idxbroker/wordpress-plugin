<template>
    <idx-fullscreen customClass="gs">
        <idx-block className="gs__hero">
            <template v-if="success">
                <idx-block className="gs__icon">
                    <svg-icon icon="fal-check" />
                </idx-block>
                <h1>Setup Complete!</h1>
            </template>
            <template v-else-if="loading">
                <idx-block className="spinner-border" role="status">
                    <idx-block tag="span" className="visually-hidden">Loading...</idx-block>
                </idx-block>
                <h1>Saving your settings</h1>
            </template>
            <template v-else-if="error">
                <idx-block className="gs__icon--error">
                    <svg-icon icon="exclamation-triangle" />
                </idx-block>
                <span>
                    <h1>Error</h1>
                    <span>We're experiencing a problem, please try again.</span>
                </span>
            </template>
        </idx-block>
    </idx-fullscreen>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import SvgIcon from '@/components/SvgIcon.vue'
export default {
    inject: [
        PRODUCT_REFS.general.repo,
        PRODUCT_REFS.listingsSettings.repo,
        PRODUCT_REFS.agentSettings.repo,
        PRODUCT_REFS.socialPro.repo
    ],
    components: {
        SvgIcon
    },
    data () {
        return {
            timeout: '',
            error: false,
            success: false,
            loading: false
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
        })
    },
    async mounted () {
        const errorList = []
        this.loading = true
        Promise.all(this.changedModules.map(async x => {
            this.module = x.module
            const repo = PRODUCT_REFS[this.module].repo
            try {
                x.path !== '' ? await this[repo].post(x.changes, x.path) : await this[repo].post(x.changes)
            } catch (error) {
                errorList.push(error)
            }
            this.setItem({
                key: x.module,
                value: {
                    changes: {},
                    module: x.module,
                    path: x.path
                }
            })
        })).then(() => {
            this.setItem({ key: 'hasChanges', value: false })
            this.success = errorList.length === 0
            this.error = errorList.length > 0
            this.loading = false
            this.timeout = setTimeout(() => {
                this.$router.push({ path: '/settings/general' }, () => {
                    location.reload()
                })
            }, 2000)
        })
    },
    beforeDestroy () {
        clearTimeout(this.timeout)
    }
}
</script>

<style scoped lang="scss">
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/fullscreen';

    .gs,
    .gs .gs__hero,
    .gs .gs__icon {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .gs {
        bottom: 0;
        height: auto;
        left: 0;
        right: 0;
        top: 0;
        width: auto;

        &.fullscreen {
            z-index: 9999;
        }

        &__hero {
            flex-direction: column;
            gap: var(--space-7);
            text-align: center;

            h1 {
                letter-spacing: var(--letter-spacing-h1);
                text-transform: uppercase;
            }
        }

        &__icon {
            border: 5px solid;
            border-radius: 50%;
            font-size: 80px;
            height: 156px;
            width: 156px;
            &--error {
                font-size: 80px;
                svg {
                    width: 1.5em;
                }
            }
        }
        .spinner-border {
            position: unset;
            height: 4rem;
            width: 4rem;
            border-right-color: $white;
        }
    }
</style>
