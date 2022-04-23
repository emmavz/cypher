<script>
import { RouterLink, RouterView } from 'vue-router'
import Footer from '@/components/Footer.vue'
export default {
  name: "App",
  components: {
    Footer,
  },
}
</script>

<template>

  <router-view></router-view>

</template>

<style lang="scss">
@import '@/assets/styles.scss';
</style>
