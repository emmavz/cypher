<script>
import Footer from "@/components/Footer.vue";
export default {
  name: "App",
  components: {
    Footer,
  },
};
</script>

<template>
  <main class="app">
    <router-view></router-view>

    <Footer />
  </main>
</template>

<style lang="scss">
@import "@/assets/styles.scss";
</style>
