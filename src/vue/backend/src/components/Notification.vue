<template>
    <transition :name="`${$idxStrap.prefix}fade`">
        <idx-block
            v-show="notification.show"
            :className="{
                'notification': true,
                'notification--error': notification.error
            }">
                <idx-block
                    className="notification__text"
                >
                    {{ notification.error ? 'We\'re experiencing a problem, please try again.' : notification.text }}
                </idx-block>
            </idx-block>
    </transition>
</template>
<script>
export default {
    name: 'notification',
    props: {
        notification: {
            type: Object,
            default: () => {}
        }
    }
}
</script>
<style lang="scss">
.notification {
    background-color: $cyan;
    color: $white;
    font-weight: 700;
    padding: var(--space-5);
    text-align: center;
    &--error {
        background-color: $red;
    }
}
</style>
