// Import libraries and stuff
import Vue from 'vue'
import Vuex from 'vuex'
import App from './App.vue'
import router from './router'
import store from './store'
import IDXStrapClass from '@idxbrokerllc/idxstrap/dist/idxStrap.js'
import serviceContainer from './serviceContainer'

// Import VCL base and variable styles
import '@idxbrokerllc/idxstrap/dist/styles/base.scss'

// Import VCL components
import {
    IdxBlock,
    IdxButton,
    IdxCard,
    IdxCardBody,
    IdxCardHeader,
    IdxCheckboxLabel,
    IdxContainer,
    IdxCustomSelect,
    IdxDialog,
    IdxDialogActions,
    IdxDialogContent,
    IdxDialogDismiss,
    IdxDialogHeader,
    IdxFormGroup,
    IdxFormInput,
    IdxFormLabel,
    IdxFormSelect,
    IdxFullscreen,
    IdxHeader,
    IdxIcon,
    IdxInputTagAutocomplete,
    IdxList,
    IdxListItem,
    IdxNavbar,
    IdxNavbarBrand,
    IdxNavItem,
    IdxNavList,
    IdxProgressBar,
    IdxProgressStepper,
    IdxRichSelect,
    IdxSinglePropertyCard,
    IdxTabContainer,
    IdxTextarea,
    IdxToggleSlider,
    IdxVArrow,
    IdxVIcon,
    IdxVNav
} from '@idxbrokerllc/idxstrap'
const idxConfig = require('../idx.config')
const pluginOptions = {
    prefix: idxConfig.options.prefix,
    separator: idxConfig.options.separator,
    applyPrefix: true
}
Vue.use(Vuex)
const idxstrap = new IDXStrapClass(pluginOptions)
Vue.mixin({
    created () {
        Vue[idxstrap.name] = idxstrap
        Vue.prototype[`$${idxstrap.name}`] = idxstrap
    }
})

const components = [
    IdxBlock,
    IdxButton,
    IdxCard,
    IdxCardBody,
    IdxCardHeader,
    IdxCheckboxLabel,
    IdxContainer,
    IdxCustomSelect,
    IdxDialog,
    IdxDialogActions,
    IdxDialogContent,
    IdxDialogDismiss,
    IdxDialogHeader,
    IdxFormGroup,
    IdxFormInput,
    IdxFormLabel,
    IdxFormSelect,
    IdxFullscreen,
    IdxHeader,
    IdxIcon,
    IdxInputTagAutocomplete,
    IdxList,
    IdxListItem,
    IdxNavbar,
    IdxNavbarBrand,
    IdxNavItem,
    IdxNavList,
    IdxProgressBar,
    IdxProgressStepper,
    IdxRichSelect,
    IdxSinglePropertyCard,
    IdxTabContainer,
    IdxTextarea,
    IdxToggleSlider,
    IdxVArrow,
    IdxVIcon,
    IdxVNav
]
components.forEach(component => Vue.component(component.name, component))

Vue.config.productionTip = false

new Vue({
    router,
    store,
    provide: serviceContainer,
    render: h => h(App)
}).$mount('#app')
