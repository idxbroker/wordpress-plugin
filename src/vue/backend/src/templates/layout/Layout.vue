<template>
    <div :id="`${$idxStrap.prefix}layout`">
        <Navbar></Navbar>
        <Page>
            <Generic v-if="loadContent"/>
            <idx-block v-else className="section">
                <idx-skeleton-screen :loading="true" >
                    <template v-slot:skeletonScreen>
                        <idx-block className="sk-grow--25"/>
                        <idx-block className="sk-grow" />
                        <idx-block className="sk-grow" />
                        <idx-block className="sk-grow" />
                        <idx-block className="sk-grow" />
                    </template>
                </idx-skeleton-screen>
            </idx-block>
        </Page>
    </div>
</template>
<script>
import Navbar from '@/templates/navbar'
import Page from '@/templates/page'
import Generic from '@/templates/layout/Generic'
import { mapState } from 'vuex'
export default {
    components: {
        Generic,
        Navbar,
        Page
    },
    computed: {
        ...mapState({
            loadContent: state => state.alerts.loadContent
        })
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/vNav';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/skeletonScreen';
</style>
