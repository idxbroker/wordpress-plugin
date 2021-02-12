<template>
  <idx-block className="app">
    <Navbar></Navbar>
    <page>
        <transition name="router" mode="out-in">
            <router-view></router-view>
        </transition>
    </page>
  </idx-block>
</template>
<script>
import Navbar from './templates/navbar'
import Page from './templates/page'
export default {
    name: 'app',
    components: {
        Navbar,
        Page
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/vNav';
</style>
