// Import libraries and stuff
import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import IDXStrapClass from '@idxbrokerllc/idxstrap/dist/idxStrap.js'

// Import VCL base and variable styles
import '@idxbrokerllc/idxstrap/dist/styles/base.scss'

// Import VCL components
import { IdxBlock, IdxButton, IdxCard, IdxCardBody, IdxCardHeader, IdxList, IdxListItem, IdxSinglePropertyCard, IdxCheckboxLabel } from '@idxbrokerllc/idxstrap'

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

const components = [IdxBlock, IdxButton, IdxCard, IdxCardBody, IdxCardHeader, IdxList, IdxListItem, IdxSinglePropertyCard, IdxCheckboxLabel]

components.forEach(component => Vue.component(component.name, component))

Vue.config.productionTip = false

new Vue({
    router,
    store,
    render: h => h(App)
}).$mount('#app')
