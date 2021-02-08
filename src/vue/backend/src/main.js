// Import libraries and stuff
import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import IDXStrapClass from '@idxbrokerllc/idxstrap/dist/idxStrap.js'

// Import VCL base and variable styles
import '@idxbrokerllc/idxstrap/dist/styles/base.scss'
import '@idxbrokerllc/idxstrap/dist/styles/globalVariables.scss'

// Import VCL components
import { IdxBlock, IdxTabContainer, IdxButton } from '@idxbrokerllc/idxstrap'

const idxConfig = require('../idx.config')
const pluginOptions = {
    prefix: idxConfig.options.prefix,
    separator: idxConfig.options.separator,
    applyPrefix: true
}

const idxstrap = new IDXStrapClass(pluginOptions)
Vue.mixin({
    created () {
        Vue[idxstrap.name] = idxstrap
        Vue.prototype[`$${idxstrap.name}`] = idxstrap
    }
})

Vue.component(IdxBlock.name, IdxBlock)
Vue.component(IdxTabContainer.name, IdxTabContainer)
Vue.component(IdxButton.name, IdxButton)

Vue.config.productionTip = false

new Vue({
    router,
    store,
    render: h => h(App)
}).$mount('#app')
