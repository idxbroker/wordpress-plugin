<template>
    <idx-fullscreen customClass="gs">
        <idx-block className="gs__hero">
            <idx-block className="gs__icon">
                <svg-icon icon="fal-check" />
            </idx-block>
            <h1>Setup Complete!</h1>
        </idx-block>
    </idx-fullscreen>
</template>

<script>
import SvgIcon from '@/components/SvgIcon.vue'
export default {
    components: {
        SvgIcon
    },
    data () {
        return {
            timeout: ''
        }
    },
    mounted () {
        this.timeout = setTimeout(() => { this.$router.push({ path: '/' }) }, 3000)
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
        }
    }
</style>
