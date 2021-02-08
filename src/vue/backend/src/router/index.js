import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'

Vue.use(VueRouter)

const routes = [
    {
        path: '/',
        name: 'Home',
        component: Home
    },
    {
        path: '/warnings',
        name: 'Warnings',
        component: () => import(/* webpackChunkName: "warnings" */ '../views/Warnings.vue')
    }
]

const router = new VueRouter({
    routes
})

export default router
