<template>
  <idx-block className="app">
    <Navbar></Navbar>
    <page>
        <transition name="router" mode="out-in">
            <GeneralSettings/>
        </transition>
    </page>
  </idx-block>
</template>
<script>
import GeneralSettings from './templates/GeneralSettings.vue'
import Navbar from './templates/navbar'
import Page from './templates/page'
export default {
    name: 'app',
    components: {
        GeneralSettings,
        Navbar,
        Page
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/vNav';
.v-nav { display: none !important;}
</style>
