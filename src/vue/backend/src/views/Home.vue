<template>
    <div class="home">
        <img alt="Vue logo" src="../assets/logo.png">
        <APIKey/>
        <HelloWorld msg="Welcome to Your Vue.js App"/>
    </div>
</template>

<script>
// @ is an alias to /src
import HelloWorld from '@/components/HelloWorld.vue'
import APIKey from '@/components/APIKey.vue'

export default {
    name: 'Home',
    components: {
        HelloWorld,
        APIKey
    }
}
</script>
