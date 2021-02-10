<template>
    <idx-block className="page" tag="main">
        <slot></slot>
    </idx-block>
</template>

<script>
export default {
    name: 'Page'
}
</script>

<style scoped lang="scss">
    .page {
        background-color: #fff;
        grid-area: content;
        padding: 20px;
    }

    @media only screen and (min-width: 500px)  {
        .page {
            border: 25px solid $gray-200;
            padding: 40px;
        }
    }

    @media only screen and (min-width: 900px)   {
        .page {
            border-width: 35px;
            padding: 60px;
        }
    }
</style>
