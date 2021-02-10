<template>
    <idx-navbar customClass="header" tag="header" type="light" theme="light">
        <idx-navbar-brand customClass="mr-auto" link="/home">
            <img src="@/assets/idx-logo.svg" height="30" alt="IDX Broker" loading="lazy">
        </idx-navbar-brand>
        <idx-block>
            <idx-button
                rounded
                theme="success"
                customClass="my-2 my-sm-0"
                type="submit">
                Hello Guest
            </idx-button>
        </idx-block>
    </idx-navbar>
</template>

<script>
export default {
    name: 'Header',
    data () {
        return {
            expanded: true
        }
    },
    methods: {
        horizontalExpand: function (e) {
            this.expanded = !this.expanded
        },
        iconBarExpand: function (e) {
            this.expanded = true
        }
    }
}
</script>

<style scoped lang="scss">
    @import '~@idxbrokerllc/idxstrap/dist/styles/globalVariables.scss';
    @import '~@idxbrokerllc/idxstrap/dist/styles/base.scss';
    @import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons';
    @import '~bootstrap/scss/navbar';

    :root {
        --font-size-h3: 21px;
        --font-size-p-large: 18px;
        --letter-spacing-h3: 2.1px;
        --line-height-h3: 22px;
        --line-height-p-large: 28px;
        --space-4: 16px;
        --space-6: 24px;
        --space-10: 40px;
    }

    .header {
        grid-area: header;
    }

    .mr-auto {
        margin-right: auto;
    }
</style>
