// Import libraries and stuff
import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import IDXStrapClass from '@idxbrokerllc/idxstrap/dist/idxStrap.js'
import { library } from '@fortawesome/fontawesome-svg-core'

// Import VCL base and variable styles
import '@idxbrokerllc/idxstrap/dist/styles/base.scss'

// Import VCL components
import { IdxBlock, IdxButton } from '@idxbrokerllc/idxstrap'

// Import Font Awesome Icons
import { faExclamationTriangle } from '@fortawesome/pro-light-svg-icons'
import { FontAwesomeIcon, FontAwesomeLayers, FontAwesomeLayersText } from '@fortawesome/vue-fontawesome'

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

const components = [IdxBlock, IdxButton]

components.forEach(component => Vue.component(component.name, component))

// Add Font Awesome Components
library.add(faExclamationTriangle)
Vue.component('font-awesome-icon', FontAwesomeIcon)
Vue.component('font-awesome-layers', FontAwesomeLayers)
Vue.component('font-awesome-layers-text', FontAwesomeLayersText)

Vue.config.productionTip = false

new Vue({
    router,
    store,
    render: h => h(App)
}).$mount('#app')
