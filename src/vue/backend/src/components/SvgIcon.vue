<template>
    <svg
        :class="['idx-wp-icon', 'idx-wp-icon-' + icon, { 'idx-wp-icon-spin': spin }]"
        role="image"
        aria-hidden="true"
        focusable="false">
        <use :xlink:href="`#${icon}`" />
    </svg>
</template>

<script>
export default {
    name: 'SvgIcon',
    props: {
        icon: {
            type: String,
            required: true
        },
        spin: {
            type: Boolean,
            default: false
        }
    }
}
</script>

<style>
svg.icon {
    fill: currentColor;
    height: 1em;
    vertical-align: -0.125em;
    width: 1em;
}
svg.icon-spin {
    animation: icon-spin 2s infinite linear;
}
@keyframes icon-spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(359deg);
    }
}
</style>
