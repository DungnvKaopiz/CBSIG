<template>
  <div>
    <!-- Header with logout -->
    <Header />
    
    <!-- Menu Component -->
    <Menu />
  </div>
</template>

<script>
import Header from './layout/Header.vue';
import Menu from './Menu.vue';

export default {
  name: 'App',
  components: {
    Header,
    Menu,
  },
};
</script>

<style scoped>
</style>
