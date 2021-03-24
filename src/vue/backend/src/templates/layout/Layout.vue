<template>
    <div :id="`${$idxStrap.prefix}layout`">
        <Navbar></Navbar>
        <Page>
            <Generic />
        </Page>
    </div>
</template>
<script>
import Navbar from '@/templates/navbar'
import Page from '@/templates/page'
import Generic from '@/templates/layout/Generic'
export default {
    components: {
        Generic,
        Navbar,
        Page
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/vNav';
</style>
