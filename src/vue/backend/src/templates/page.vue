<template>
    <idx-block className="content-page">
        <slot></slot>
    </idx-block>
</template>
<style lang="scss">
    .content-page {
        padding: 50px;
        margin-left: 255px;
        margin-top: 83px;
        background-color: #fafafa;

        p.lead {
            font-weight: 400;
        }
    }
</style>
